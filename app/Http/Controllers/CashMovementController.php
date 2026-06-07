<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Services\CashMovementService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashMovementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(request()->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para ver Caja.');

        $perPage = $this->perPage($request);
        $search = trim((string) $request->query('q', ''));
        $tipo = (string) $request->query('tipo', '');
        $concepto = (string) $request->query('concepto', '');
        $metodo = (string) $request->query('metodo_pago', '');
        $estado = (string) $request->query('estado', '');
        $fechaDesde = (string) $request->query('fecha_desde', '');
        $fechaHasta = (string) $request->query('fecha_hasta', '');

        $movimientos = UrbanizacionContext::cashMovements(CashMovement::with('cliente', 'venta', 'reserva', 'cuota'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('concepto', 'like', "%{$search}%")
                        ->orWhere('referencia', 'like', "%{$search}%")
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search): void {
                            $clienteQuery->where('nombre', 'like', "%{$search}%")
                                ->orWhere('documento', 'like', "%{$search}%");
                        });
                });
            })
            ->when($tipo !== '', fn ($query) => $query->where('tipo', $tipo))
            ->when($concepto !== '', fn ($query) => $query->where('concepto', $concepto))
            ->when($metodo !== '', fn ($query) => $query->where('metodo_pago', $metodo))
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($fechaDesde !== '', fn ($query) => $query->whereDate('fecha', '>=', $fechaDesde))
            ->when($fechaHasta !== '', fn ($query) => $query->whereDate('fecha', '<=', $fechaHasta))
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return view('caja.index', [
            'movimientos' => $movimientos,
            'filters' => [
                'q' => $search,
                'tipo' => $tipo,
                'concepto' => $concepto,
                'metodo_pago' => $metodo,
                'estado' => $estado,
                'fecha_desde' => $fechaDesde,
                'fecha_hasta' => $fechaHasta,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function annul(\Illuminate\Http\Request $request, CashMovement $cashMovement, CashMovementService $cashMovementService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::cashMovementBelongsToCurrent($cashMovement), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate(['motivo' => ['required', 'string', 'max:500']]);
        $cashMovementService->annul($cashMovement, $data['motivo']);

        return back()->with('status', 'Movimiento de caja anulado.');
    }

    private function perPage(Request $request): int
    {
        $perPage = $request->integer('per_page', 15);

        return in_array($perPage, [15, 30, 50, 100], true) ? $perPage : 15;
    }
}
