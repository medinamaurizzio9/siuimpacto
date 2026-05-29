<?php

namespace App\Http\Controllers;

use App\Models\Urbanizacion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDisponibilidadController extends Controller
{
    public function __invoke(Request $request): View
    {
        $urbanizaciones = Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get();
        $urbanizacion = Urbanizacion::with('manzanos.lotes')
            ->where('estado', 'activa')
            ->find($request->integer('urbanizacion_id') ?: $urbanizaciones->first()?->id);

        return view('disponibilidad.index', compact('urbanizaciones', 'urbanizacion'));
    }
}
