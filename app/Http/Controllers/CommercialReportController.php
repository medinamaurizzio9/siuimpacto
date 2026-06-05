<?php

namespace App\Http\Controllers;

use App\Models\GrupoComercial;
use App\Models\Reserva;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Models\Venta;
use App\Services\AuditService;
use App\Services\CommercialAccessService;
use App\Services\SystemSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CommercialReportController extends Controller
{
    public function index(Request $request, CommercialAccessService $access): View
    {
        [$ventas, $reservas] = $this->results($request, $access);

        return view('reportes.comercial', [
            ...$this->viewData($request, $access, $ventas, $reservas),
            'ventas' => $ventas,
            'reservas' => $reservas,
        ]);
    }

    public function excel(Request $request, CommercialAccessService $access, AuditService $audit): Response
    {
        [$ventas, $reservas] = $this->results($request, $access);
        $audit->log(null, 'exportar_reporte_comercial', 'Reporte comercial exportado a Excel.', null, $request->query(), $request);

        return response()
            ->view('reportes.exports.comercial-excel', [...$this->metrics($ventas, $reservas), 'ventas' => $ventas])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="reporte-comercial-inmolider.xls"');
    }

    public function pdf(Request $request, CommercialAccessService $access, AuditService $audit, SystemSettingsService $settings): Response
    {
        [$ventas, $reservas] = $this->results($request, $access);
        $audit->log(null, 'exportar_reporte_comercial', 'Reporte comercial exportado a PDF.', null, $request->query(), $request);

        return Pdf::loadView('pdf.reporte-comercial', [
            ...$this->metrics($ventas, $reservas),
            'ventas' => $ventas,
            'settings' => $settings->all(),
        ])->download('reporte-comercial-inmolider.pdf');
    }

    private function results(Request $request, CommercialAccessService $access): array
    {
        $ventasQuery = Venta::with('urbanizacion', 'grupoComercial', 'supervisorComercial', 'supervisorVentas', 'vendedor', 'creador', 'cliente', 'lote.manzano');
        $reservasQuery = Reserva::with('urbanizacion', 'grupoComercial', 'vendedor', 'cliente', 'lote.manzano');
        $access->applyVentas($ventasQuery, $request->user());
        $access->applyReservas($reservasQuery, $request->user());
        $this->applyFilters($ventasQuery, $reservasQuery, $request, $access);

        return [
            $ventasQuery->latest('fecha_venta')->get(),
            $reservasQuery->latest('fecha_reserva')->get(),
        ];
    }

    private function applyFilters(Builder $ventas, Builder $reservas, Request $request, CommercialAccessService $access): void
    {
        if ($request->filled('urbanizacion_id')) {
            abort_unless($access->canAccessUrbanizacion($request->user(), $request->integer('urbanizacion_id')), 403);
            $ventas->where('urbanizacion_id', $request->integer('urbanizacion_id'));
            $reservas->where('urbanizacion_id', $request->integer('urbanizacion_id'));
        }
        foreach (['grupo_comercial_id', 'supervisor_comercial_id', 'supervisor_ventas_id', 'vendedor_id'] as $field) {
            if ($request->filled($field)) {
                $ventas->where($field, $request->integer($field));
                $reservas->where($field, $request->integer($field));
            }
        }
        if ($request->filled('tipo_venta')) {
            $ventas->where('tipo_venta', $request->query('tipo_venta'));
        }
        if ($request->filled('estado_venta')) {
            $ventas->where('estado', $request->query('estado_venta'));
        }
        if ($request->filled('estado_reserva')) {
            $reservas->where('estado', $request->query('estado_reserva'));
        }
        if ($request->filled('desde')) {
            $ventas->whereDate('fecha_venta', '>=', $request->query('desde'));
            $reservas->whereDate('fecha_reserva', '>=', $request->query('desde'));
        }
        if ($request->filled('hasta')) {
            $ventas->whereDate('fecha_venta', '<=', $request->query('hasta'));
            $reservas->whereDate('fecha_reserva', '<=', $request->query('hasta'));
        }
    }

    private function viewData(Request $request, CommercialAccessService $access, Collection $ventas, Collection $reservas): array
    {
        $visibleIds = $access->visibleUserIds($request->user());
        $users = User::with('roles')->when($visibleIds !== null, fn ($query) => $query->whereIn('id', $visibleIds))->orderBy('name')->get();

        return [
            ...$this->metrics($ventas, $reservas),
            'urbanizaciones' => Urbanizacion::whereIn('id', $access->accessibleUrbanizacionIds($request->user()))->orderBy('nombre')->get(),
            'grupos' => $access->gruposVisibles($request->user()),
            'supervisoresComerciales' => $users->filter(fn (User $user) => $user->hasRole('supervisor comercial')),
            'supervisoresVentas' => $users->filter(fn (User $user) => $user->hasAnyRole(['supervisor ventas', 'supervisor'])),
            'vendedores' => $users->filter(fn (User $user) => $user->hasRole('vendedor')),
        ];
    }

    private function metrics(Collection $ventas, Collection $reservas): array
    {
        $validas = $ventas->whereIn('estado', ['activa', 'completada']);
        $reservasTotal = $reservas->count();

        return [
            'metricas' => [
                'vendidos' => $validas->count(),
                'reservas_activas' => $reservas->where('estado', 'activa')->count(),
                'contado' => $validas->where('tipo_venta', 'contado')->count(),
                'credito' => $validas->where('tipo_venta', 'credito')->count(),
                'monto' => (float) $validas->sum('monto_total'),
                'conversion' => $reservasTotal > 0 ? round(($reservas->where('estado', 'convertida')->count() / $reservasTotal) * 100, 2) : 0,
            ],
            'porGrupo' => $validas->groupBy(fn (Venta $venta) => $venta->grupoComercial?->nombre ?? 'Sin grupo')->map->count()->sortDesc(),
            'porUrbanizacion' => $validas->groupBy(fn (Venta $venta) => $venta->urbanizacion?->nombre ?? 'Sin urbanización')->map->count()->sortDesc(),
            'porVendedor' => $validas->groupBy(fn (Venta $venta) => $venta->vendedor?->name ?? $venta->creador?->name ?? 'Sin vendedor')->map->count()->sortDesc(),
        ];
    }
}
