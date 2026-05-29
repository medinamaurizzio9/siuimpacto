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
    public function index(): View
    {
        return view('caja.index', [
            'movimientos' => UrbanizacionContext::cashMovements(CashMovement::with('cliente', 'venta', 'reserva', 'cuota'))->latest()->paginate(25),
        ]);
    }

    public function annul(\Illuminate\Http\Request $request, CashMovement $cashMovement, CashMovementService $cashMovementService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::cashMovementBelongsToCurrent($cashMovement), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate(['motivo' => ['required', 'string', 'max:500']]);
        $cashMovementService->annul($cashMovement, $data['motivo']);

        return back()->with('status', 'Movimiento de caja anulado.');
    }
}
