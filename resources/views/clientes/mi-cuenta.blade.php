@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Mi cuenta</h1></div>
<div class="card">
    <h2>{{ $cliente->nombre }}</h2>
    <p class="muted">{{ $cliente->documento }} · {{ $cliente->telefono }} · {{ $cliente->email }}</p>
</div>
<div class="card" style="margin-top:18px;">
    <h2>Mis lotes y plan de pagos</h2>
    <table class="table">
        <thead><tr><th>Lote</th><th>Precio</th><th>Anticipo</th><th>Saldo</th><th>Estado</th><th>PDF</th></tr></thead>
        <tbody>
        @foreach($cliente->ventas as $venta)
            <tr>
                <td>{{ $venta->lote->manzano->urbanizacion->nombre }} / {{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td>
                <td>{{ number_format($venta->precio_final, 2) }}</td>
                <td>{{ number_format($venta->cuota_inicial, 2) }}</td>
                <td>{{ number_format($venta->cuotas->sum('saldo_pendiente'), 2) }}</td>
                <td><span class="badge {{ $venta->estado }}">{{ $venta->estado }}</span></td>
                <td><a class="btn secondary" href="{{ route('pdf.plan', $venta) }}">Plan de pagos</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="card" style="margin-top:18px;">
    <h2>Mis reservas</h2>
    <table class="table"><tbody>@foreach($cliente->reservas as $reserva)<tr><td>{{ $reserva->lote->manzano->codigo }}-{{ $reserva->lote->codigo }}</td><td>{{ $reserva->fecha_vencimiento->format('d/m/Y') }}</td><td><span class="badge {{ $reserva->estado }}">{{ $reserva->estado }}</span></td></tr>@endforeach</tbody></table>
</div>
<div class="card" style="margin-top:18px;">
    <h2>Mis pagos</h2>
    <table class="table"><tbody>@foreach($cliente->cashMovements as $movimiento)<tr><td>{{ $movimiento->fecha->format('d/m/Y') }}</td><td>{{ $movimiento->concepto }}</td><td>{{ number_format($movimiento->monto, 2) }}</td><td>{{ $movimiento->estado }}</td></tr>@endforeach</tbody></table>
</div>
@endsection
