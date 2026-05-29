<?php

namespace App\Http\Controllers;

use App\Models\Urbanizacion;
use App\Models\Lote;
use App\Support\UrbanizacionContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MapaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $urbanizaciones = UrbanizacionContext::accessibleUrbanizaciones($request->user());

        if ($request->filled('urbanizacion_id')) {
            $requestedId = $request->integer('urbanizacion_id');
            abort_unless(UrbanizacionContext::userCanAccess($request->user(), $requestedId), 403, 'No tienes acceso a esta urbanizacion');
            $request->session()->put('urbanizacion_id', $requestedId);
        }

        $urbanizacion = Urbanizacion::with('manzanos.lotes')
            ->find(UrbanizacionContext::currentId());

        $estado = $request->query('estado');
        $manzanoId = $request->integer('manzano_id');
        $busqueda = $request->query('lote');
        $sinUbicacion = $request->boolean('sin_ubicacion');

        return view('mapa.index', compact('urbanizaciones', 'urbanizacion', 'estado', 'manzanoId', 'busqueda', 'sinUbicacion'));
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
