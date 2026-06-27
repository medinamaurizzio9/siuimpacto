<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Asesor;
use App\Models\Cliente;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DeletionDependencyService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(Request $request): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $perPage = $this->perPage($request);
        $search = trim((string) $request->query('q', ''));
        $ventas = (string) $request->query('ventas', '');
        $sort = $this->sort($request);

        $query = UrbanizacionContext::clientes(Cliente::withCount('ventas')->with('createdBy'), $urbanizacionId);
        $this->applyVisibility($query, $request->user());

        if ($request->filled('usuario_id') && $this->canFilterAsesor($request->user())) {
            $query->where('created_by', $request->integer('usuario_id'));
        }

        $clientes = $query
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('nombre', 'like', "%{$search}%")
                        ->orWhere('documento', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($ventas === 'con_ventas', fn ($query) => $query->has('ventas'))
            ->when($ventas === 'sin_ventas', fn ($query) => $query->doesntHave('ventas'))
            ->when($sort['field'] === 'ventas', fn ($query) => $query->orderBy('ventas_count', $sort['direction']), fn ($query) => $query->orderBy($sort['field'], $sort['direction']))
            ->paginate($perPage)
            ->appends($request->query());

        return view('clientes.index', [
            'clientes' => $clientes,
            'asesores' => $this->asesoresForFilter($request->user()),
            'canFilterAsesor' => $this->canFilterAsesor($request->user()),
            'filters' => [
                'q' => $search,
                'ventas' => $ventas,
                'per_page' => $perPage,
                'usuario_id' => $request->integer('usuario_id') ?: null,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('clientes.form', [
            'cliente' => new Cliente(),
            'loteId' => $request->integer('lote_id') ?: null,
        ]);
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $query = UrbanizacionContext::clientes(Cliente::query());
        $this->applyVisibility($query, $request->user());

        $clientes = $query
            ->where(function ($query) use ($q): void {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('documento', 'like', "%{$q}%")
                    ->orWhere('telefono', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->limit(10)
            ->get(['id', 'nombre', 'documento', 'telefono', 'email']);

        return response()->json($clientes);
    }

    public function store(StoreClienteRequest $request, AuditService $auditService): RedirectResponse
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $data = $request->validated();
        $loteId = $request->integer('lote_id') ?: null;
        unset($data['lote_id']);
        $documento = trim((string) ($data['documento'] ?? ''));

        if ($documento !== '') {
            $duplicate = Cliente::with('createdBy')
                ->where('urbanizacion_id', $urbanizacionId)
                ->where('documento', $documento)
                ->first();

            if ($duplicate) {
                return back()
                    ->withInput()
                    ->with('duplicate_cliente_id', $duplicate->id)
                    ->with('duplicate_cliente_message', 'Cliente ya registrado. No es necesario volver a registrarlo.')
                    ->with('duplicate_cliente_data', $this->duplicateData($duplicate))
                    ->with('duplicate_lote_id', $loteId);
            }
        }

        $cliente = Cliente::create([
            ...$data,
            'urbanizacion_id' => $urbanizacionId,
            'created_by' => $request->user()->id,
        ]);
        $auditService->log($cliente, 'crear_cliente', 'Cliente creado.', null, $cliente->toArray(), $request);

        return redirect()->route('clientes.index')->with('status', 'Cliente creado.');
    }

    public function show(Cliente $cliente): View
    {
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
        abort_unless($this->canSeeCliente($cliente, request()->user()), 403, 'No tienes acceso a este cliente.');

        $cliente->load('createdBy', 'urbanizacion', 'ventas.lote.manzano', 'ventas.cuotas', 'reservas.lote.manzano');

        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente): View
    {
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
        abort_unless($this->canSeeCliente($cliente, request()->user()), 403, 'No tienes acceso a este cliente.');

        return view('clientes.form', compact('cliente'));
    }

    public function update(StoreClienteRequest $request, Cliente $cliente, AuditService $auditService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
        abort_unless($this->canSeeCliente($cliente, $request->user()), 403, 'No tienes acceso a este cliente.');

        $before = $cliente->toArray();
        $cliente->update($request->validated());
        $auditService->log($cliente, 'editar_cliente', 'Cliente actualizado.', $before, $cliente->fresh()->toArray(), $request);

        return redirect()->route('clientes.index')->with('status', 'Cliente actualizado.');
    }

    public function destroy(\Illuminate\Http\Request $request, Cliente $cliente, AuditService $auditService, DeletionDependencyService $dependencyService): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para eliminar clientes.');
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
        abort_unless($this->canSeeCliente($cliente, $request->user()), 403, 'No tienes acceso a este cliente.');

        $dependencies = $dependencyService->forCliente($cliente);
        if ($dependencyService->hasDependencies($dependencies)) {
            return back()->withErrors([
                'delete' => $dependencyService->message('el cliente', $dependencies),
            ]);
        }

        $before = $cliente->toArray();
        $auditService->log($cliente, 'eliminar_cliente', 'Cliente eliminado.', $before, null, $request);
        $cliente->delete();

        return back()->with('status', 'Cliente eliminado.');
    }

    private function duplicateData(Cliente $cliente): array
    {
        return [
            'id' => $cliente->id,
            'nombre' => $cliente->nombre,
            'documento' => $cliente->documento,
            'created_by' => $cliente->createdBy?->name ?? 'Usuario no registrado',
            'created_at' => $cliente->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    private function perPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 50);

        return in_array($perPage, [10, 15, 25, 50, 100], true) ? $perPage : 50;
    }

    private function applyVisibility($query, User $user): void
    {
        $ids = $this->visibleCreatorIds($user);

        if ($ids !== null) {
            $query->whereIn('created_by', $ids);
        }
    }

    private function visibleCreatorIds(User $user): ?array
    {
        if ($user->hasAnyRole(['super administrador', 'administrador', 'gerente'])) {
            return null;
        }

        if ($user->hasRole('supervisor')) {
            $ids = Asesor::where('supervisor_id', $user->id)->pluck('user_id')->all();
            $ids[] = $user->id;

            return array_values(array_unique($ids));
        }

        if ($user->hasAnyRole(['asesor', 'vendedor'])) {
            return [$user->id];
        }

        return [];
    }

    private function canSeeCliente(Cliente $cliente, User $user): bool
    {
        $ids = $this->visibleCreatorIds($user);

        return $ids === null || in_array((int) $cliente->created_by, $ids, true);
    }

    private function canFilterAsesor(User $user): bool
    {
        return $user->hasAnyRole(['super administrador', 'administrador', 'gerente']);
    }

    private function asesoresForFilter(User $user)
    {
        if (! $this->canFilterAsesor($user)) {
            return collect();
        }

        return User::whereHas('roles', fn ($query) => $query->whereIn('name', ['vendedor', 'asesor', 'supervisor']))
            ->orderBy('name')
            ->get();
    }

    private function sort(Request $request): array
    {
        $allowedSorts = ['nombre', 'documento', 'telefono', 'email', 'ventas'];
        $field = $request->query('sort', 'nombre');

        return [
            'field' => in_array($field, $allowedSorts, true) ? $field : 'nombre',
            'direction' => $request->query('direction') === 'desc' ? 'desc' : 'asc',
        ];
    }
}
