<?php

namespace App\Http\Controllers;

use App\Models\GrupoComercial;
use App\Models\User;
use App\Models\Urbanizacion;
use App\Services\CommercialAccessService;
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
        $query = GrupoComercial::with('supervisor', 'urbanizaciones')
            ->withCount([
                'asesores',
                'usuarios as supervisores_ventas_count' => fn ($query) => $query->where('grupo_comercial_user.tipo', 'supervisor_ventas')->where('grupo_comercial_user.activo', true),
                'ventas as terrenos_vendidos_count' => fn ($query) => $query->whereIn('estado', ['activa', 'completada']),
                'reservas as reservas_activas_count' => fn ($query) => $query->where('estado', 'activa'),
                'ventas as ventas_contado_count' => fn ($query) => $query->where('tipo_venta', 'contado')->whereIn('estado', ['activa', 'completada']),
                'ventas as ventas_credito_count' => fn ($query) => $query->where('tipo_venta', 'credito')->whereIn('estado', ['activa', 'completada']),
            ])
            ->withSum(['ventas as monto_vendido' => fn ($query) => $query->whereIn('estado', ['activa', 'completada'])], 'monto_total')
            ->latest();

        if (! app(CommercialAccessService::class)->isGlobal($request->user())) {
            $query->whereIn('id', app(CommercialAccessService::class)->gruposVisibles($request->user())->pluck('id'));
        }

        return view('grupos-comerciales.index', [
            'grupos' => $query->paginate(15),
        ]);
    }

    public function show(Request $request, GrupoComercial $grupoComercial): View
    {
        $this->authorizeView($request, $grupoComercial);
        $grupoComercial->load([
            'supervisor',
            'urbanizaciones',
            'usuarios',
            'asesores.user',
            'ventas' => fn ($query) => $query->with('cliente', 'lote.manzano.urbanizacion', 'vendedor')->latest('fecha_venta')->limit(10),
            'reservas' => fn ($query) => $query->with('cliente', 'lote.manzano.urbanizacion', 'vendedor')->where('estado', 'activa')->latest('fecha_reserva')->limit(10),
        ]);

        $ventasActivas = $grupoComercial->ventas()->whereIn('estado', ['activa', 'completada']);

        return view('grupos-comerciales.show', [
            'grupo' => $grupoComercial,
            'metricas' => [
                'vendidos' => (clone $ventasActivas)->count(),
                'reservas' => $grupoComercial->reservas()->where('estado', 'activa')->count(),
                'contado' => (clone $ventasActivas)->where('tipo_venta', 'contado')->count(),
                'credito' => (clone $ventasActivas)->where('tipo_venta', 'credito')->count(),
                'monto' => (float) (clone $ventasActivas)->sum('monto_total'),
            ],
            'porUrbanizacion' => (clone $ventasActivas)->selectRaw('urbanizacion_id, count(*) total')->with('urbanizacion')->groupBy('urbanizacion_id')->get(),
            'porVendedor' => (clone $ventasActivas)->selectRaw('vendedor_id, count(*) total')->with('vendedor')->groupBy('vendedor_id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('grupos-comerciales.form', $this->formData(new GrupoComercial(['activo' => true])));
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request);
        $grupo = GrupoComercial::create($data);
        if ($grupo->supervisor_id) {
            $grupo->usuarios()->syncWithoutDetaching([$grupo->supervisor_id => ['tipo' => 'supervisor_comercial', 'activo' => true]]);
        }
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
        if ($grupoComercial->supervisor_id) {
            $grupoComercial->usuarios()->syncWithoutDetaching([$grupoComercial->supervisor_id => ['tipo' => 'supervisor_comercial', 'activo' => true]]);
        }
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

    public function assignments(Request $request, GrupoComercial $grupoComercial): View
    {
        abort_unless($request->user()->hasRole('super administrador'), 403, 'Solo el Super Administrador puede asignar urbanizaciones a grupos.');

        return view('grupos-comerciales.asignaciones', [
            'grupo' => $grupoComercial,
            'urbanizaciones' => Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get(),
            'asignadas' => $grupoComercial->urbanizaciones()->pluck('urbanizaciones.id')->all(),
        ]);
    }

    public function updateAssignments(Request $request, GrupoComercial $grupoComercial, AuditService $auditService): RedirectResponse
    {
        abort_unless($request->user()->hasRole('super administrador'), 403, 'Solo el Super Administrador puede asignar urbanizaciones a grupos.');
        $data = $request->validate([
            'urbanizaciones' => ['nullable', 'array'],
            'urbanizaciones.*' => ['integer', 'exists:urbanizaciones,id'],
        ]);
        $before = $grupoComercial->urbanizaciones()->pluck('urbanizaciones.id')->all();
        $sync = collect($data['urbanizaciones'] ?? [])->mapWithKeys(fn ($id) => [(int) $id => ['activo' => true]])->all();
        $grupoComercial->urbanizaciones()->sync($sync);
        $auditService->log($grupoComercial, 'asignar_urbanizaciones_grupo', 'Asignaciones de urbanización del grupo actualizadas.', ['urbanizaciones' => $before], ['urbanizaciones' => $data['urbanizaciones'] ?? []], $request);

        return redirect()->route('grupos-comerciales.show', $grupoComercial)->with('status', 'Urbanizaciones asignadas correctamente.');
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
            'supervisores' => User::role(['supervisor comercial', 'supervisor'])->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?GrupoComercial $grupo = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('grupos_comerciales', 'nombre')->ignore($grupo)],
            'descripcion' => ['nullable', 'string'],
            'observaciones' => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }

    private function authorizeView(Request $request, GrupoComercial $grupo): void
    {
        if (app(CommercialAccessService::class)->isGlobal($request->user())) {
            return;
        }

        abort_unless(app(CommercialAccessService::class)->gruposVisibles($request->user())->contains('id', $grupo->id), 403, 'No tienes acceso a este grupo comercial.');
    }
}
