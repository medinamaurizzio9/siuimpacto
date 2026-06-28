<?php

namespace App\Http\Controllers;

use App\Models\Urbanizacion;
use App\Models\Lote;
use App\Services\CommercialSettingsService;
use App\Services\GeoPlanCalibrationService;
use App\Services\LotPricingService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Illuminate\View\View;

class MapaController extends Controller
{
    public function __invoke(Request $request, CommercialSettingsService $settings): View
    {
        $urbanizaciones = UrbanizacionContext::accessibleUrbanizaciones($request->user());

        if ($request->filled('urbanizacion_id')) {
            $requestedId = $request->integer('urbanizacion_id');
            abort_unless(UrbanizacionContext::userCanAccess($request->user(), $requestedId), 403, 'No tienes acceso a esta urbanizacion');
            $request->session()->put('urbanizacion_id', $requestedId);
        }

        $urbanizacion = Urbanizacion::with([
            'manzanos.lotes',
            'referencias' => fn ($query) => $query->where('activo', true)->orderBy('nombre'),
        ])
            ->find(UrbanizacionContext::currentId());

        $estado = $request->query('estado');
        $manzanoId = $request->integer('manzano_id');
        $busqueda = $request->query('lote');
        $sinUbicacion = $request->boolean('sin_ubicacion');

        $commercialConfig = $urbanizacion
            ? $settings->calculatorPayload($urbanizacion->id)
            : $settings->calculatorPayload(null);

        return view('mapa.index', compact('urbanizaciones', 'urbanizacion', 'estado', 'manzanoId', 'busqueda', 'sinUbicacion', 'commercialConfig'));
    }

    public function updateLotePosition(Request $request, Lote $lote): JsonResponse
    {
        abort_unless($request->user()->hasRole('administrador'), 403, 'Solo el administrador puede editar posiciones.');
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate([
            'coord_x' => ['required', 'numeric', 'min:0', 'max:100'],
            'coord_y' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $lote->update($data);

        return response()->json([
            'message' => 'Lote ubicado correctamente',
            'coord_x' => $lote->coord_x,
            'coord_y' => $lote->coord_y,
        ]);
    }

    public function loteJson(Request $request, Lote $lote, LotPricingService $pricingService): JsonResponse
    {
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $lote->loadMissing('manzano.urbanizacion');
        $pricePayload = $pricingService->payload($lote);
        $user = $request->user();
        $isVendedor = $user?->hasRole('vendedor');
        $isSupervisor = $user?->hasRole('supervisor');

        $canReservar = $lote->estado === 'disponible' && ($user?->can('crear reservas') ?? false);
        $canVender = ! $isVendedor && ! $isSupervisor && ($user?->can('crear ventas') ?? false) && in_array($lote->estado, ['disponible', 'reservado'], true);
        $canEditar = ! $isVendedor && ! $isSupervisor && ($user?->can('editar lotes') ?? false);
        $canCalculadora = ($user?->can('crear reservas') ?? false)
            || ($user?->can('crear ventas') ?? false)
            || ($user?->can('editar lotes') ?? false);

        return response()->json([
            'urbanizacion' => $lote->manzano->urbanizacion->nombre,
            'manzano' => $lote->manzano->codigo,
            'lote' => $lote->codigo,
            'label' => $lote->manzano->codigo.'-'.$lote->codigo,
            'superficie' => number_format((float) $lote->superficie, 2).' m2',
            'precio' => $pricingService->formatUsd($pricePayload['credit_usd']),
            'precio_bs' => $pricingService->formatBs($pricePayload['credit_bs']),
            'precio_real_usd' => $pricePayload['credit_usd'],
            'precio_real_bs' => $pricePayload['credit_bs'],
            'tipo_cambio_usd_bs' => $pricePayload['tipo_cambio_usd_bs'],
            'cuota_inicial' => $pricingService->formatUsd($pricePayload['initial_credit_usd']),
            'cuota_inicial_bs' => $pricingService->formatBs($pricePayload['initial_credit_bs']),
            'estado' => $lote->estado,
            'urls' => [
                'detalle' => route('lotes.show', $lote),
                'reservar' => route('reservas.create', ['lote_id' => $lote->id]),
                'vender' => route('ventas.create', ['lote_id' => $lote->id]),
                'editar' => route('lotes.edit', $lote),
            ],
            'permisos' => [
                'reservar' => $canReservar,
                'vender' => $canVender,
                'editar' => $canEditar,
                'calculadora' => $canCalculadora,
            ],
        ]);
    }

    public function myLocation(Request $request, GeoPlanCalibrationService $calibrationService): JsonResponse
    {
        $data = $request->validate([
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $urbanizacion = Urbanizacion::findOrFail(UrbanizacionContext::currentId());

        try {
            $position = $calibrationService->gpsToPlanPosition($urbanizacion, (float) $data['latitud'], (float) $data['longitud']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'x' => $position['x'],
            'y' => $position['y'],
            'accuracy' => isset($data['accuracy']) ? round((float) $data['accuracy'], 2) : null,
        ]);
    }

    public function clearLotePosition(Request $request, Lote $lote): JsonResponse
    {
        abort_unless($request->user()->hasRole('administrador'), 403, 'Solo el administrador puede editar posiciones.');
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $lote->update([
            'coord_x' => null,
            'coord_y' => null,
        ]);

        return response()->json([
            'message' => 'Ubicacion quitada correctamente',
        ]);
    }
}
