<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MiCuentaController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->cliente_id, 403, 'Tu usuario no esta vinculado a un cliente.');

        $cliente = $request->user()->cliente()
            ->with('ventas.lote.manzano.urbanizacion', 'ventas.cuotas', 'reservas.lote.manzano', 'cashMovements')
            ->firstOrFail();

        return view('clientes.mi-cuenta', compact('cliente'));
    }
}
