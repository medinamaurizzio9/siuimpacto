<?php

namespace App\Http\Controllers;

use App\Models\GrupoComercial;
use App\Models\User;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrupoComercialController extends Controller
{
    public function index(Request $request): View
    {
        $query = GrupoComercial::with('supervisor', 'asesores')->latest();

        if ($request->user()->hasRole('supervisor')) {
            $query->where('supervisor_id', $request->user()->id);
        }

        $this->applyIndexFilters($query, $request);

        return view('grupos-comerciales.index', [
            'grupos' => $query->paginate(15)->appends($request->query()),
            'supervisores' => $request->user()->hasRole('supervisor')
                ? User::whereKey($request->user()->id)->get()
                : User::role('supervisor')->orderBy('name')->get(),
        ]);
    }

    private function applyIndexFilters($query, Request $request): void
    {
        if ($request->filled('buscar')) {
            $term = trim((string) $request->query('buscar'));
            $query->where('nombre', 'like', "%{$term}%");
        }

        if ($request->filled('supervisor_id') && ! $request->user()->hasRole('supervisor')) {
            $query->where('supervisor_id', $request->integer('supervisor_id'));
        }

        if (in_array($request->query('estado'), ['activo', 'inactivo'], true)) {
            $query->where('activo', $request->query('estado') === 'activo');
        }
    }

    public function create(): View
    {
        return view('grupos-comerciales.form', $this->formData(new GrupoComercial(['activo' => true])));
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request);
        $grupo = GrupoComercial::create($data);
        $auditService->log($grupo, 'crear_grupo_comercial', 'Grupo comercial creado.', null, $grupo->toArray(), $request);

        return redirect()->route('grupos-comerciales.index')->with('status', 'Grupo comercial creado.');
    }

    public function edit(GrupoComercial $grupoComercial): View
    {
        return view('grupos-comerciales.form', $this->formData($grupoComercial));
    }

    public function update(Request $request, GrupoComercial $grupoComercial, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request, $grupoComercial);
        $before = $grupoComercial->toArray();
        $grupoComercial->update($data);
        $auditService->log($grupoComercial, 'editar_grupo_comercial', 'Grupo comercial actualizado.', $before, $grupoComercial->fresh()->toArray(), $request);

        return redirect()->route('grupos-comerciales.index')->with('status', 'Grupo comercial actualizado.');
    }

    public function destroy(Request $request, GrupoComercial $grupoComercial, AuditService $auditService): RedirectResponse
    {
        $before = $grupoComercial->toArray();
        $grupoComercial->update(['activo' => false]);
        $auditService->log($grupoComercial, 'desactivar_grupo_comercial', 'Grupo comercial desactivado.', $before, $grupoComercial->fresh()->toArray(), $request);

        return back()->with('status', 'Grupo comercial desactivado.');
    }

    public function excel(): Response
    {
        return response()
            ->view('grupos-comerciales.export', ['grupos' => GrupoComercial::with('supervisor', 'asesores')->get()])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="grupos-comerciales-impacto.xls"');
    }

    public function pdf(): Response
    {
        return Pdf::loadView('grupos-comerciales.export', ['grupos' => GrupoComercial::with('supervisor', 'asesores')->get()])->download('grupos-comerciales-impacto.pdf');
    }

    private function formData(GrupoComercial $grupo): array
    {
        return [
            'grupo' => $grupo,
            'supervisores' => User::role('supervisor')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?GrupoComercial $grupo = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('grupos_comerciales', 'nombre')->ignore($grupo)],
            'descripcion' => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }
}
