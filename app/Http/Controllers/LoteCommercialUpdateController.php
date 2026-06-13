<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Services\AuditService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoteCommercialUpdateController extends Controller
{
    public function updateQuick(Request $request, Lote $lote, AuditService $auditService): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate([
            'precio_oportunidad_usd' => ['required', 'numeric', 'min:0'],
            'cuota_inicial_valor' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($lote, $data, $request, $auditService): void {
            $before = $lote->only(['precio', 'cuota_inicial_tipo', 'cuota_inicial_valor']);
            $lote->update([
                'precio' => (float) $data['precio_oportunidad_usd'],
                'cuota_inicial_tipo' => 'monto',
                'cuota_inicial_valor' => (float) $data['cuota_inicial_valor'],
            ]);

            $auditService->log($lote, 'edicion_rapida_precio_oportunidad', 'Edicion rapida de precio oportunidad del lote.', [
                'lote_id' => $lote->id,
                'campo' => 'precio',
                'valor_anterior' => $before['precio'],
            ], [
                'lote_id' => $lote->id,
                'campo' => 'precio',
                'valor_nuevo' => $lote->precio,
            ], $request);

            $auditService->log($lote, 'edicion_rapida_cuota_inicial', 'Edicion rapida de cuota inicial del lote.', [
                'lote_id' => $lote->id,
                'campo' => 'cuota_inicial_valor',
                'valor_anterior' => $before['cuota_inicial_valor'],
            ], [
                'lote_id' => $lote->id,
                'campo' => 'cuota_inicial_valor',
                'valor_nuevo' => $lote->cuota_inicial_valor,
            ], $request);
        });

        return back()->with('status', 'Datos comerciales del lote actualizados correctamente.');
    }

    public function bulkUpdate(Request $request, AuditService $auditService): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'lote_ids' => ['required', 'array', 'min:1'],
            'lote_ids.*' => ['integer', 'distinct', 'exists:lotes,id'],
            'operation' => ['required', 'in:reemplazar_precio_oportunidad,incrementar_precio_oportunidad_monto,incrementar_precio_oportunidad_porcentaje,reemplazar_cuota,incrementar_cuota_monto'],
            'valor' => ['required', 'numeric', 'min:0'],
        ], [
            'lote_ids.required' => 'Seleccione al menos un lote.',
            'lote_ids.min' => 'Seleccione al menos un lote.',
        ]);

        $ids = collect($data['lote_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $lotes = UrbanizacionContext::lotes(Lote::query())
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();
        abort_unless($lotes->count() === $ids->count(), 403, 'No tienes acceso a uno o mas lotes seleccionados.');

        $value = (float) ($data['valor'] ?? 0);

        DB::transaction(function () use ($lotes, $data, $value, $auditService, $request): void {
            foreach ($lotes as $lote) {
                $before = $lote->only(['precio', 'cuota_inicial_tipo', 'cuota_inicial_valor']);
                $updates = [];
                $accion = null;

                match ($data['operation']) {
                    'reemplazar_precio_oportunidad' => [$updates, $accion] = [[
                        'precio' => $value,
                    ], 'actualizacion_masiva_precio_oportunidad'],
                    'incrementar_precio_oportunidad_monto' => [$updates, $accion] = [[
                        'precio' => max(0, (float) $lote->precio + $value),
                    ], 'actualizacion_masiva_precio_oportunidad'],
                    'incrementar_precio_oportunidad_porcentaje' => [$updates, $accion] = [[
                        'precio' => round((float) $lote->precio * (1 + $value / 100), 2),
                    ], 'actualizacion_masiva_precio_oportunidad'],
                    'reemplazar_cuota' => [$updates, $accion] = [[
                        'cuota_inicial_tipo' => 'monto',
                        'cuota_inicial_valor' => $value,
                    ], 'actualizacion_masiva_cuota_inicial'],
                    'incrementar_cuota_monto' => [$updates, $accion] = [[
                        'cuota_inicial_tipo' => 'monto',
                        'cuota_inicial_valor' => max(0, (float) $lote->cuota_inicial_valor + $value),
                    ], 'actualizacion_masiva_cuota_inicial'],
                };

                $lote->update($updates);
                $fresh = $lote->fresh();
                $auditField = array_key_exists('precio', $updates) ? 'precio' : 'cuota_inicial_valor';
                $auditService->log($lote, $accion, 'Actualizacion comercial masiva de lote.', [
                    'lote_id' => $lote->id,
                    'campo' => $auditField,
                    'valor_anterior' => $before[$auditField] ?? null,
                ], [
                    'lote_id' => $lote->id,
                    'campo' => $auditField,
                    'valor_nuevo' => $fresh->{$auditField},
                    'operacion' => $data['operation'],
                ], $request);
            }
        });

        return back()->with('status', 'Actualizacion masiva aplicada a '.$lotes->count().' lotes.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);
    }
}
