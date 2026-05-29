<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\Cliente;
use App\Models\Cuota;
use App\Models\Lote;
use App\Models\Reserva;
use App\Models\Venta;
use App\Support\UrbanizacionContext;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function __invoke(string $tipo): Response
    {
        $map = [
            'lotes' => [UrbanizacionContext::lotes(Lote::with('manzano.urbanizacion'))->get(), ['urbanizacion', 'manzano', 'lote', 'estado', 'superficie', 'precio']],
            'clientes' => [Cliente::all(), ['nombre', 'documento', 'telefono', 'email']],
            'ventas' => [UrbanizacionContext::ventas(Venta::with('cliente', 'lote.manzano'))->get(), ['fecha', 'cliente', 'lote', 'precio_final', 'estado']],
            'cuotas' => [UrbanizacionContext::cuotas(Cuota::with('venta.cliente'))->get(), ['cliente', 'numero', 'monto', 'pagado', 'saldo', 'estado']],
            'reservas' => [UrbanizacionContext::reservas(Reserva::with('cliente', 'lote'))->get(), ['cliente', 'lote', 'vence', 'monto', 'estado']],
            'caja' => [UrbanizacionContext::cashMovements(CashMovement::with('cliente'))->get(), ['fecha', 'cliente', 'tipo', 'concepto', 'monto', 'estado']],
        ];

        abort_unless(isset($map[$tipo]), 404);
        [$rows, $headers] = $map[$tipo];
        $csv = implode(',', $headers)."\n";

        foreach ($rows as $row) {
            $csv .= implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"', $this->values($tipo, $row)))."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$tipo.'-impacto.csv"',
        ]);
    }

    private function values(string $tipo, mixed $row): array
    {
        return match ($tipo) {
            'lotes' => [$row->manzano->urbanizacion->nombre, $row->manzano->codigo, $row->codigo, $row->estado, $row->superficie, $row->precio],
            'clientes' => [$row->nombre, $row->documento, $row->telefono, $row->email],
            'ventas' => [$row->fecha_venta?->format('Y-m-d'), $row->cliente->nombre, $row->lote->manzano->codigo.'-'.$row->lote->codigo, $row->precio_final, $row->estado],
            'cuotas' => [$row->venta->cliente->nombre, $row->numero, $row->monto, $row->monto_pagado, $row->saldo_pendiente, $row->estado],
            'reservas' => [$row->cliente->nombre, $row->lote->codigo, $row->fecha_vencimiento?->format('Y-m-d'), $row->monto_reserva, $row->estado],
            'caja' => [$row->fecha?->format('Y-m-d'), $row->cliente?->nombre, $row->tipo, $row->concepto, $row->monto, $row->estado],
        };
    }
}
