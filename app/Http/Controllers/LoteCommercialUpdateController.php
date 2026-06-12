<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Services\AuditService;
use App\Support\UrbanizacionContext;
use Illuminate\Database\Eloquent\Builder;
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
            'scope' => ['required', 'in:actual,todos,filtrados,manzano'],
            'manzano_id' => ['nullable', 'integer', 'exists:manzanos,id'],
            'operation' => ['required', 'in:reemplazar_precio_oportunidad,incrementar_precio_oportunidad_monto,incrementar_precio_oportunidad_porcentaje,reemplazar_cuota,incrementar_cuota_monto'],
            'valor' => ['required', 'numeric', 'min:0'],
            'buscar' => ['nullable', 'string'],
            'estado' => ['nullable', 'string'],
            'superficie_desde' => ['nullable', 'numeric', 'min:0'],
            'superficie_hasta' => ['nullable', 'numeric', 'min:0'],
            'precio_desde' => ['nullable', 'numeric', 'min:0'],
            'precio_hasta' => ['nullable', 'numeric', 'min:0'],
        ]);

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
