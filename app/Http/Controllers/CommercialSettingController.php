<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\CommercialSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialSettingController extends Controller
{
    public function edit(CommercialSettingsService $settings): View
    {
        return view('admin.configuracion-comercial', [
            'reservaDiasHabilesAsesor' => $settings->reservaDiasHabilesAsesor(),
            'priceSettings' => $settings->priceSettings(),
        ]);
    }

    public function update(Request $request, CommercialSettingsService $settings, AuditService $auditService): RedirectResponse
    {
        $request->merge([
            'tipo_cambio_usd_bs' => $request->input('tipo_cambio_usd_bs', $settings->tipoCambioUsdBs()),
            'incremento_credito_tipo' => $request->input('incremento_credito_tipo', $settings->incrementoCreditoTipo()),
            'incremento_credito_valor' => $request->input('incremento_credito_valor', $settings->incrementoCreditoValor()),
        ]);

        $data = $request->validate([
            'reserva_dias_habiles_asesor' => ['required', 'integer', 'min:1', 'max:30'],
            'tipo_cambio_usd_bs' => ['required', 'numeric', 'min:0'],
            'incremento_credito_tipo' => ['required', 'in:monto,porcentaje'],
            'incremento_credito_valor' => ['required', 'numeric', 'min:0'],
        ]);

        $before = [
            'reserva_dias_habiles_asesor' => $settings->reservaDiasHabilesAsesor(),
            ...$settings->priceSettings(),
        ];
        $setting = $settings->setReservaDiasHabilesAsesor((int) $data['reserva_dias_habiles_asesor']);
        $settings->setPriceSettings($data);
        $auditService->log($setting, 'actualizar_configuracion_comercial', 'Administrador actualizo configuracion comercial.', $before, $data, $request);

        return back()->with('status', 'Configuracion comercial actualizada.');
    }
}
