@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Estado de cuenta por cliente</h1></div>

<form class="card form" method="GET">
    <div class="field"><label>Cliente</label><select name="cliente_id"><option value="">Seleccionar cliente</option>@foreach($clientes as $item)<option value="{{ $item->id }}" @selected($cliente?->id === $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>&nbsp;</label><button class="btn">Consultar</button></div>
</form>

@if($cliente)
    @php
        $ventas = $cliente->ventas;
        $cuotas = $ventas->flatMap->cuotas;
        $pagadas = $cuotas->where('estado', 'pagada');
        $pendientes = $cuotas->whereIn('estado', ['pendiente', 'parcial', 'vencida']);
        $saldo = $pendientes->sum('saldo_pendiente');
    @endphp
    <div class="grid stats" style="margin-top:18px;">
        <div class="card"><div class="muted">Ventas del cliente</div><div class="stat-value">{{ $ventas->count() }}</div></div>
        <div class="card"><div class="muted">Cuotas pagadas</div><div class="stat-value">{{ $pagadas->count() }}</div></div>
        <div class="card"><div class="muted">Cuotas pendientes</div><div class="stat-value">{{ $pendientes->count() }}</div></div>
        <div class="card"><div class="muted">Saldo total pendiente</div><div class="stat-value">{{ number_format($saldo, 2) }}</div></div>
    </div>
    <div class="card" style="margin-top:18px;" data-report="estado-cuenta">
        <h2>{{ $cliente->nombre }}</h2>
        <p><strong>Documento:</strong> {{ $cliente->documento }} <strong>Telefono:</strong> {{ $cliente->telefono }}</p>
        <table class="table"><thead><tr><th>Venta</th><th>Lote adquirido</th><th>Precio</th><th>Cuotas pagadas</th><th>Cuotas pendientes</th><th>Saldo</th></tr></thead><tbody>
            @foreach($ventas as $venta)
                @php($ventaCuotas = $venta->cuotas)
                <tr><td>#{{ $venta->id }}</td><td>{{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td><td>{{ number_format($venta->precio_final, 2) }}</td><td>{{ $ventaCuotas->where('estado', 'pagada')->count() }}</td><td>{{ $ventaCuotas->whereIn('estado', ['pendiente', 'parcial', 'vencida'])->count() }}</td><td>{{ number_format($ventaCuotas->sum('saldo_pendiente'), 2) }}</td></tr>
            @endforeach
        </tbody></table>
        <div class="actions" style="margin-top:14px;">@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'cuotas') }}">Exportar CSV</a>@endcan</div>
    </div>
@else
    <div class="card" style="margin-top:18px;" data-report="estado-cuenta-vacio">Selecciona un cliente para cargar su estado de cuenta.</div>
@endif
@endsection
