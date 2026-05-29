<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CashMovement;
use App\Models\Cuota;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\Venta;
use App\Support\UrbanizacionContext;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $lotesQuery = fn () => UrbanizacionContext::lotes(Lote::query(), $urbanizacionId);
        $cashQuery = fn () => UrbanizacionContext::cashMovements(CashMovement::query(), $urbanizacionId);
        $ventasQuery = fn () => UrbanizacionContext::ventas(Venta::query(), $urbanizacionId);
        $cuotasQuery = fn () => UrbanizacionContext::cuotas(Cuota::query(), $urbanizacionId);
        $reservasQuery = fn () => UrbanizacionContext::reservas(Reserva::query(), $urbanizacionId);

        $lotesPorEstado = $lotesQuery()->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');
        $ingresosPorMes = $cashQuery()->where('tipo', 'ingreso')
            ->where('estado', 'confirmado')
            ->whereDate('fecha', '>=', now()->subMonths(5)->startOfMonth())
            ->get()
            ->groupBy(fn (CashMovement $movement) => $movement->fecha->format('M Y'))
            ->map(fn (Collection $items) => $items->sum('monto'));

        return view('dashboard', [
            'totalLotes' => $lotesQuery()->count(),
            'lotesDisponibles' => $lotesQuery()->where('estado', 'disponible')->count(),
            'lotesVendidos' => $lotesQuery()->where('estado', 'vendido')->count(),
            'lotesReservados' => $lotesQuery()->where('estado', 'reservado')->count(),
            'lotesBloqueados' => $lotesQuery()->where('estado', 'bloqueado')->count(),
            'ingresosDia' => $cashQuery()->where('tipo', 'ingreso')->where('estado', 'confirmado')->whereDate('fecha', today())->sum('monto'),
            'ingresosMes' => $cashQuery()->where('tipo', 'ingreso')->where('estado', 'confirmado')->whereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()])->sum('monto'),
            'clientes' => Cliente::count(),
            'ventas' => $ventasQuery()->with('cliente', 'lote.manzano')->latest()->take(6)->get(),
            'cuotasVencidas' => $cuotasQuery()->whereIn('estado', ['pendiente', 'parcial', 'vencida'])->whereDate('fecha_programada', '<', now())->count(),
            'reservasVencidas' => $reservasQuery()->where('estado', 'activa')->whereDate('fecha_vencimiento', '<', now())->count(),
            'lotesPorEstado' => $lotesPorEstado,
            'ingresosPorMes' => $ingresosPorMes,
            'cuotasVencidasLista' => $cuotasQuery()->with('venta.cliente', 'venta.lote.manzano')
                ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
                ->whereDate('fecha_programada', '<', now())
                ->orderBy('fecha_programada')
                ->take(6)
                ->get(),
            'reservasPorVencer' => $reservasQuery()->with('cliente', 'lote.manzano')
                ->where('estado', 'activa')
                ->whereBetween('fecha_vencimiento', [now()->startOfDay(), now()->addDays(10)->endOfDay()])
                ->orderBy('fecha_vencimiento')
                ->take(6)
                ->get(),
        ]);
    }
}
