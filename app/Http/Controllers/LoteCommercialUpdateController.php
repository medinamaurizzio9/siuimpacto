<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Services\AuditService;
use App\Services\LotPricingService;
use App\Support\UrbanizacionContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoteCommercialUpdateController extends Controller
{
    public function updateQuick(Request $request, Lote $lote, AuditService $auditService): RedirectResponse
    {
        $this->authorizeAdmin($request);
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate([
            'precio_real_usd' => ['required', 'numeric', 'min:0'],
            'cuota_inicial_valor' => ['required', 'numeric', 'min:0'],
        ]);

        if ((float) $data['precio_real_usd'] < (float) $lote->precio) {
            throw ValidationException::withMessages([
                'precio_real_usd' => 'El precio real no puede ser menor al precio oportunidad.',
            ]);
        }

        DB::transaction(function () use ($lote, $data, $request, $auditService): void {
            $before = $lote->only(['precio_real_override_usd', 'cuota_inicial_tipo', 'cuota_inicial_valor']);
            $lote->update([
                'precio_real_override_usd' => (float) $data['precio_real_usd'],
                'cuota_inicial_tipo' => 'monto',
                'cuota_inicial_valor' => (float) $data['cuota_inicial_valor'],
            ]);

            $auditService->log($lote, 'edicion_rapida_precio_real', 'Edicion rapida de precio real del lote.', [
                'lote_id' => $lote->id,
                'campo' => 'precio_real_override_usd',
                'valor_anterior' => $before['precio_real_override_usd'],
            ], [
                'lote_id' => $lote->id,
                'campo' => 'precio_real_override_usd',
                'valor_nuevo' => $lote->precio_real_override_usd,
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

    public function bulkUpdate(Request $request, LotPricingService $pricingService, AuditService $auditService): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'scope' => ['required', 'in:actual,todos,filtrados,manzano'],
            'manzano_id' => ['nullable', 'integer', 'exists:manzanos,id'],
            'operation' => ['required', 'in:reemplazar_cuota,incrementar_cuota_monto,reemplazar_precio_real,incrementar_precio_real_monto,incrementar_precio_real_porcentaje,limpiar_precio_real'],
            'valor' => ['nullable', 'numeric', 'min:0'],
            'buscar' => ['nullable', 'string'],
            'estado' => ['nullable', 'string'],
            'superficie_desde' => ['nullable', 'numeric', 'min:0'],
            'superficie_hasta' => ['nullable', 'numeric', 'min:0'],
            'precio_desde' => ['nullable', 'numeric', 'min:0'],
            'precio_hasta' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['operation'] !== 'limpiar_precio_real' && ! $request->filled('valor')) {
            throw ValidationException::withMessages(['valor' => 'Ingresa el valor para aplicar la actualizacion masiva.']);
        }

        if ($data['scope'] === 'manzano') {
            $request->validate(['manzano_id' => ['required', 'integer', 'exists:manzanos,id']]);
            abort_unless(
                \App\Models\Manzano::whereKey($request->integer('manzano_id'))->where('urbanizacion_id', UrbanizacionContext::currentId())->exists(),
                403,
                'No tienes acceso a este manzano.'
            );
        }

        $lotes = $this->bulkQuery($request)->orderBy('id')->get();
        $value = (float) ($data['valor'] ?? 0);

        DB::transaction(function () use ($lotes, $data, $value, $pricingService, $auditService, $request): void {
            foreach ($lotes as $lote) {
                $before = $lote->only(['precio_real_override_usd', 'cuota_inicial_tipo', 'cuota_inicial_valor']);
                $updates = [];
                $accion = null;

                match ($data['operation']) {
                    'reemplazar_cuota' => [$updates, $accion] = [[
                        'cuota_inicial_tipo' => 'monto',
                        'cuota_inicial_valor' => $value,
                    ], 'actualizacion_masiva_cuota_inicial'],
                    'incrementar_cuota_monto' => [$updates, $accion] = [[
                        'cuota_inicial_tipo' => 'monto',
                        'cuota_inicial_valor' => max(0, (float) $lote->cuota_inicial_valor + $value),
                    ], 'actualizacion_masiva_cuota_inicial'],
                    'reemplazar_precio_real' => [$updates, $accion] = [[
                        'precio_real_override_usd' => $value,
                    ], 'actualizacion_masiva_precio_real'],
                    'incrementar_precio_real_monto' => [$updates, $accion] = [[
                        'precio_real_override_usd' => $pricingService->creditUsd($lote) + $value,
                    ], 'actualizacion_masiva_precio_real'],
                    'incrementar_precio_real_porcentaje' => [$updates, $accion] = [[
                        'precio_real_override_usd' => round($pricingService->creditUsd($lote) * (1 + $value / 100), 2),
                    ], 'actualizacion_masiva_precio_real'],
                    'limpiar_precio_real' => [$updates, $accion] = [[
                        'precio_real_override_usd' => null,
                    ], 'limpiar_precio_real_personalizado'],
                };

                if (array_key_exists('precio_real_override_usd', $updates) && $updates['precio_real_override_usd'] !== null && (float) $updates['precio_real_override_usd'] < (float) $lote->precio) {
                    throw ValidationException::withMessages([
                        'valor' => 'El precio real no puede ser menor al precio oportunidad del lote '.$lote->codigo.'.',
                    ]);
                }

                $lote->update($updates);
                $auditService->log($lote, $accion, 'Actualizacion comercial masiva de lote.', [
                    'lote_id' => $lote->id,
                    'campo' => array_key_first($updates),
                    'valor_anterior' => $before[array_key_first($updates)] ?? null,
                ], [
                    'lote_id' => $lote->id,
                    'campo' => array_key_first($updates),
                    'valor_nuevo' => $lote->fresh()->{array_key_first($updates)},
                    'operacion' => $data['operation'],
                ], $request);
            }
        });

        return back()->with('status', 'Actualizacion masiva aplicada a '.$lotes->count().' lotes.');
    }

    private function bulkQuery(Request $request): Builder
    {
        $query = UrbanizacionContext::lotes(Lote::query());
        $scope = $request->input('scope');

        if ($scope === 'manzano') {
            $query->where('manzano_id', $request->integer('manzano_id'));
        }

        if (in_array($scope, ['actual', 'filtrados'], true)) {
            $this->applyFilters($query, $request);
        }

        return $query;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->filled('buscar'), fn ($query) => $query->where('codigo', 'like', '%'.$request->string('buscar')->trim().'%'))
            ->when($request->filled('manzano_id') && $request->input('scope') !== 'todos', fn ($query) => $query->where('manzano_id', $request->integer('manzano_id')))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')->toString()))
            ->when($request->filled('superficie_desde'), fn ($query) => $query->where('superficie', '>=', $request->input('superficie_desde')))
            ->when($request->filled('superficie_hasta'), fn ($query) => $query->where('superficie', '<=', $request->input('superficie_hasta')))
            ->when($request->filled('precio_desde'), fn ($query) => $query->where('precio', '>=', $request->input('precio_desde')))
            ->when($request->filled('precio_hasta'), fn ($query) => $query->where('precio', '<=', $request->input('precio_hasta')));
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);
    }
}
