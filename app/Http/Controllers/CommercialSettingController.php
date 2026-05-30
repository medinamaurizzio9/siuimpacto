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
        ]);
    }

    public function update(Request $request, CommercialSettingsService $settings, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'reserva_dias_habiles_asesor' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $before = ['reserva_dias_habiles_asesor' => $settings->reservaDiasHabilesAsesor()];
        $setting = $settings->setReservaDiasHabilesAsesor((int) $data['reserva_dias_habiles_asesor']);
        $auditService->log($setting, 'actualizar_configuracion_comercial', 'Administrador cambio dias habiles de reserva para asesores.', $before, $data, $request);

        return back()->with('status', 'Configuracion comercial actualizada.');
    }
}
