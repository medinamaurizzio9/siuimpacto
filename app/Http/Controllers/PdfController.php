<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\Venta;
use App\Support\UrbanizacionContext;
use App\Services\SystemSettingsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function receipt(Request $request, CashMovement $cashMovement, SystemSettingsService $settings)
    {
        $this->authorizeClientOrPermission($request, $cashMovement->cliente_id, 'cobrar cuotas');
        if (! $request->user()->hasRole('cliente')) {
            abort_unless(UrbanizacionContext::cashMovementBelongsToCurrent($cashMovement), 403, 'No tienes acceso a esta urbanizacion');
        }

        return Pdf::loadView('pdf.recibo', ['movimiento' => $cashMovement->load('cliente', 'venta', 'cuota'), 'settings' => $settings->all()])
            ->stream('recibo-'.$cashMovement->id.'.pdf');
    }

    public function paymentPlan(Request $request, Venta $venta, SystemSettingsService $settings)
    {
        $this->authorizeClientOrPermission($request, $venta->cliente_id, 'ver ventas');
        if (! $request->user()->hasRole('cliente')) {
            abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');
        }

        return Pdf::loadView('pdf.plan-pagos', ['venta' => $venta->load('cliente', 'lote.manzano.urbanizacion', 'cuotas'), 'settings' => $settings->all()])
            ->stream('plan-pagos-'.$venta->id.'.pdf');
    }

    public function contract(Request $request, Venta $venta, SystemSettingsService $settings)
    {
        $this->authorizeClientOrPermission($request, $venta->cliente_id, 'ver ventas');
        if (! $request->user()->hasRole('cliente')) {
            abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');
        }

        return Pdf::loadView('pdf.contrato', ['venta' => $venta->load('cliente', 'lote.manzano.urbanizacion', 'cuotas'), 'settings' => $settings->all()])
            ->stream('contrato-'.$venta->id.'.pdf');
    }

    private function authorizeClientOrPermission(Request $request, ?int $clienteId, string $permission): void
    {
        $user = $request->user();

        abort_unless($user->can($permission) || ($user->hasRole('cliente') && $user->cliente_id === $clienteId), 403);
    }
}
