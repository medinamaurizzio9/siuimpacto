<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayCuotaRequest;
use App\Models\Cuota;
use App\Services\InstallmentService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CuotaController extends Controller
{
    public function index(Request $request, InstallmentService $installmentService): View
    {
        $installmentService->markOverdue();

        $query = UrbanizacionContext::cuotas(Cuota::with('venta.cliente', 'venta.lote.manzano'))->orderBy('fecha_programada');

        if ($request->query('estado') === 'vencidas') {
            $query->where('estado', 'vencida');
        }

        return view('cuotas.index', ['cuotas' => $query->paginate(25)]);
    }

    public function update(PayCuotaRequest $request, Cuota $cuota, InstallmentService $installmentService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::cuotaBelongsToCurrent($cuota), 403, 'No tienes acceso a esta urbanizacion');

        if ($cuota->estado === 'pagada' && ! $request->user()->hasRole('administrador')) {
            throw ValidationException::withMessages(['cuota' => 'Solo un administrador puede modificar cuotas pagadas.']);
        }

        $installmentService->pay(
            $cuota,
            (float) $request->validated('monto_pagado'),
            $request->validated('metodo_pago'),
            $request->user(),
            $request->validated('referencia')
        );

        return back()->with('status', 'Pago registrado y movimiento de caja generado.');
    }
}
