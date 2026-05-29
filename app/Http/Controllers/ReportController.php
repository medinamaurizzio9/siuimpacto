<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Lote;
use App\Models\Manzano;
use App\Models\Reserva;
use App\Models\User;
use App\Support\UrbanizacionContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();

        return view('reportes.index', [
            'lotesTotal' => UrbanizacionContext::lotes(Lote::query(), $urbanizacionId)->count(),
            'reservasActivas' => UrbanizacionContext::reservas(Reserva::query(), $urbanizacionId)->where('estado', 'activa')->count(),
            'cuotasPendientes' => UrbanizacionContext::cuotas(Cuota::query(), $urbanizacionId)->whereIn('estado', ['pendiente', 'parcial', 'vencida'])->count(),
            'ingresosMes' => UrbanizacionContext::cashMovements(CashMovement::query(), $urbanizacionId)
                ->where('tipo', 'ingreso')
                ->where('estado', 'confirmado')
                ->whereBetween('fecha', [now()->startOfMonth(), now()])
                ->sum('monto'),
        ]);
    }

    public function lotesEstado(Request $request): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $query = UrbanizacionContext::lotes(Lote::with('manzano'), $urbanizacionId);

        if ($request->filled('manzano_id')) {
            $query->where('manzano_id', $request->integer('manzano_id'));
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        $lotes = $query->orderBy('manzano_id')->orderBy('codigo')->get();
        $conteos = UrbanizacionContext::lotes(Lote::query(), $urbanizacionId)
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('reportes.lotes-estado', [
            'lotes' => $lotes,
            'conteos' => $conteos,
            'manzanos' => Manzano::where('urbanizacion_id', $urbanizacionId)->orderBy('codigo')->get(),
            'estados' => Lote::ESTADOS,
        ]);
    }

    public function reservas(Request $request): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $query = UrbanizacionContext::reservas(Reserva::with('cliente', 'lote.manzano', 'usuario'), $urbanizacionId);

        $this->applyDateRange($query, $request, 'fecha_reserva');

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->integer('usuario_id'));
        }

        $reservas = $query->orderByDesc('fecha_reserva')->get();
        $base = UrbanizacionContext::reservas(Reserva::query(), $urbanizacionId);

        return view('reportes.reservas', [
            'reservas' => $reservas,
            'vendedores' => User::role(['vendedor', 'supervisor'])->orderBy('name')->get(),
            'activas' => (clone $base)->where('estado', 'activa')->count(),
            'vencidas' => (clone $base)->where('estado', 'vencida')->count(),
            'proximas' => (clone $base)->where('estado', 'activa')->whereBetween('fecha_vencimiento', [now(), now()->addDays(7)])->count(),
            'convertidas' => (clone $base)->where('estado', 'convertida')->count(),
        ]);
    }

    public function cuotas(Request $request): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $query = UrbanizacionContext::cuotas(Cuota::with('venta.cliente', 'venta.lote.manzano'), $urbanizacionId);

        $this->applyDateRange($query, $request, 'fecha_vencimiento');

        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        if ($request->filled('cliente_id')) {
            $query->whereHas('venta', fn (Builder $builder) => $builder->where('cliente_id', $request->integer('cliente_id')));
        }

        $cuotas = $query->orderBy('fecha_vencimiento')->get();
        $base = UrbanizacionContext::cuotas(Cuota::query(), $urbanizacionId);

        return view('reportes.cuotas', [
            'cuotas' => $cuotas,
            'clientes' => $this->clientesDeUrbanizacion($urbanizacionId),
            'pendientes' => (clone $base)->where('estado', 'pendiente')->count(),
            'vencidas' => (clone $base)->where('estado', 'vencida')->count(),
            'parciales' => (clone $base)->where('estado', 'parcial')->count(),
            'pagadas' => (clone $base)->where('estado', 'pagada')->count(),
            'saldoPendiente' => (clone $base)->whereIn('estado', ['pendiente', 'parcial', 'vencida'])->sum('saldo_pendiente'),
        ]);
    }

    public function ingresos(Request $request): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $query = UrbanizacionContext::cashMovements(CashMovement::with('cliente', 'user'), $urbanizacionId)->where('tipo', 'ingreso');

        $this->applyDateRange($query, $request, 'fecha');

        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->query('metodo_pago'));
        }

        if ($request->filled('concepto')) {
            $query->where('concepto', $request->query('concepto'));
        }

        $movimientos = $query->orderByDesc('fecha')->get();
        $desde = $request->filled('desde') ? Carbon::parse($request->query('desde')) : now()->startOfMonth();
        $hasta = $request->filled('hasta') ? Carbon::parse($request->query('hasta')) : now();
        $base = UrbanizacionContext::cashMovements(CashMovement::query(), $urbanizacionId)->where('tipo', 'ingreso');

        return view('reportes.ingresos', [
            'movimientos' => $movimientos,
            'metodos' => CashMovement::METODOS,
            'conceptos' => CashMovement::CONCEPTOS,
            'ingresosDia' => (clone $base)->where('estado', 'confirmado')->whereDate('fecha', now()->toDateString())->sum('monto'),
            'ingresosRango' => (clone $base)->where('estado', 'confirmado')->whereBetween('fecha', [$desde, $hasta])->sum('monto'),
            'ingresosAnulados' => (clone $base)->where('estado', 'anulado')->whereBetween('fecha', [$desde, $hasta])->sum('monto'),
            'totalNeto' => (clone $base)->where('estado', 'confirmado')->whereBetween('fecha', [$desde, $hasta])->sum('monto'),
        ]);
    }

    public function estadoCuenta(Request $request): View
    {
        $urbanizacionId = UrbanizacionContext::currentId();
        $clientes = $this->clientesDeUrbanizacion($urbanizacionId);
        $cliente = null;

        if ($request->filled('cliente_id')) {
            $cliente = Cliente::whereKey($request->integer('cliente_id'))
                ->whereIn('id', $clientes->pluck('id'))
                ->with(['ventas' => fn ($query) => UrbanizacionContext::ventas($query, $urbanizacionId)->with('lote.manzano', 'cuotas')])
                ->first();
        }

        return view('reportes.estado-cuenta', [
            'clientes' => $clientes,
            'cliente' => $cliente,
        ]);
    }

    public function exportaciones(): View
    {
        return view('reportes.exportaciones');
    }

    public function csv(Request $request, string $reporte): Response
    {
        $csv = match ($reporte) {
            'lotes-estado' => $this->lotesCsv($request),
            'reservas' => $this->reservasCsv($request),
            'cuotas' => $this->cuotasCsv($request),
            'ingresos' => $this->ingresosCsv($request),
            default => abort(404),
        };

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$reporte.'-impacto.csv"',
        ]);
    }

    private function applyDateRange(Builder $query, Request $request, string $column): void
    {
        if ($request->filled('desde')) {
            $query->whereDate($column, '>=', Carbon::parse($request->query('desde'))->toDateString());
        }

        if ($request->filled('hasta')) {
            $query->whereDate($column, '<=', Carbon::parse($request->query('hasta'))->toDateString());
        }
    }

    private function clientesDeUrbanizacion(?int $urbanizacionId)
    {
        return Cliente::whereHas('ventas.lote.manzano', fn (Builder $builder) => $builder->where('urbanizacion_id', $urbanizacionId))
            ->orderBy('nombre')
            ->get();
    }

    private function lotesCsv(Request $request): string
    {
        $query = UrbanizacionContext::lotes(Lote::with('manzano'), UrbanizacionContext::currentId());
        if ($request->filled('manzano_id')) {
            $query->where('manzano_id', $request->integer('manzano_id'));
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        return $this->csvFromRows(['manzano', 'lote', 'superficie', 'precio', 'estado'], $query->get(), fn (Lote $lote) => [
            $lote->manzano->codigo,
            $lote->codigo,
            $lote->superficie,
            $lote->precio,
            $lote->estado,
        ]);
    }

    private function reservasCsv(Request $request): string
    {
        $query = UrbanizacionContext::reservas(Reserva::with('cliente', 'lote.manzano', 'usuario'), UrbanizacionContext::currentId());
        $this->applyDateRange($query, $request, 'fecha_reserva');
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->integer('usuario_id'));
        }

        return $this->csvFromRows(['cliente', 'manzano', 'lote', 'estado', 'fecha_reserva', 'fecha_vencimiento', 'vendedor'], $query->get(), fn (Reserva $reserva) => [
            $reserva->cliente->nombre,
            $reserva->lote->manzano->codigo,
            $reserva->lote->codigo,
            $reserva->estado,
            $reserva->fecha_reserva?->format('Y-m-d'),
            $reserva->fecha_vencimiento?->format('Y-m-d'),
            $reserva->usuario?->name,
        ]);
    }

    private function cuotasCsv(Request $request): string
    {
        $query = UrbanizacionContext::cuotas(Cuota::with('venta.cliente', 'venta.lote.manzano'), UrbanizacionContext::currentId());
        $this->applyDateRange($query, $request, 'fecha_vencimiento');
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }
        if ($request->filled('cliente_id')) {
            $query->whereHas('venta', fn (Builder $builder) => $builder->where('cliente_id', $request->integer('cliente_id')));
        }

        return $this->csvFromRows(['cliente', 'venta', 'lote', 'numero', 'vence', 'monto', 'pagado', 'saldo', 'estado'], $query->get(), fn (Cuota $cuota) => [
            $cuota->venta->cliente->nombre,
            $cuota->venta_id,
            $cuota->venta->lote->manzano->codigo.'-'.$cuota->venta->lote->codigo,
            $cuota->numero,
            $cuota->fecha_vencimiento?->format('Y-m-d'),
            $cuota->monto,
            $cuota->monto_pagado,
            $cuota->saldo_pendiente,
            $cuota->estado,
        ]);
    }

    private function ingresosCsv(Request $request): string
    {
        $query = UrbanizacionContext::cashMovements(CashMovement::with('cliente', 'user'), UrbanizacionContext::currentId())->where('tipo', 'ingreso');
        $this->applyDateRange($query, $request, 'fecha');
        if ($request->filled('metodo_pago')) {
            $query->where('metodo_pago', $request->query('metodo_pago'));
        }
        if ($request->filled('concepto')) {
            $query->where('concepto', $request->query('concepto'));
        }

        return $this->csvFromRows(['fecha', 'cliente', 'concepto', 'metodo_pago', 'monto', 'usuario', 'estado'], $query->get(), fn (CashMovement $movement) => [
            $movement->fecha?->format('Y-m-d'),
            $movement->cliente?->nombre,
            $movement->concepto,
            $movement->metodo_pago,
            $movement->monto,
            $movement->user?->name,
            $movement->estado,
        ]);
    }

    private function csvFromRows(array $headers, iterable $rows, callable $mapper): string
    {
        $csv = implode(',', $headers)."\n";

        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"', $mapper($row)))."\n";
        }

        return $csv;
    }
}
