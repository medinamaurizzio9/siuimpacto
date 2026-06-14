<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Services\CommercialSettingsService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommercialSettingController extends Controller
{
    public function edit(CommercialSettingsService $settings): View
    {
        $urbanizacion = UrbanizacionContext::current();
        $urbanizacionId = UrbanizacionContext::currentId();

        return view('admin.configuracion-comercial', [
            'urbanizacion' => $urbanizacion,
            'reservaDiasHabilesAsesor' => $settings->reservaDiasHabilesAsesor($urbanizacionId),
            'priceSettings' => $settings->priceSettings($urbanizacionId),
        ]);
    }

    public function update(Request $request, CommercialSettingsService $settings, AuditService $auditService): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);

        $urbanizacionId = UrbanizacionContext::currentId();

        $request->merge([
            'tipo_cambio_usd_bs' => $request->input('tipo_cambio_usd_bs', $settings->tipoCambioUsdBs($urbanizacionId)),
            'incremento_credito_tipo' => $request->input('incremento_credito_tipo', $settings->incrementoCreditoTipo($urbanizacionId)),
            'incremento_credito_valor' => $request->input('incremento_credito_valor', $settings->incrementoCreditoValor($urbanizacionId)),
        ]);

        $data = $request->validate([
            'reserva_dias_habiles_asesor' => ['required', 'integer', 'min:1', 'max:30'],
            'tipo_cambio_usd_bs' => ['required', 'numeric', 'min:0'],
            'incremento_credito_tipo' => ['required', 'in:monto,porcentaje'],
            'incremento_credito_valor' => ['required', 'numeric', 'min:0'],
        ]);

        $before = [
            'reserva_dias_habiles_asesor' => $settings->reservaDiasHabilesAsesor($urbanizacionId),
            ...$settings->priceSettings($urbanizacionId),
        ];
        $setting = $settings->updateForUrbanizacion($urbanizacionId, $data);
        $auditService->log($setting, 'actualizar_configuracion_comercial', 'Administrador actualizo configuracion comercial.', $before, $data, $request);

        return back()->with('status', 'Configuracion comercial actualizada.');
    }
}
