@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Cliente</h1>
    <div class="actions no-print">
        <button class="btn secondary" type="button" onclick="window.print()">Imprimir</button>
        <a class="btn secondary" href="{{ route('clientes.pdf', $cliente) }}">PDF ficha cliente</a>
        <a class="btn secondary" href="{{ route('clientes.estado-cuenta.pdf', $cliente) }}">PDF estado de cuenta</a>
        <a class="btn secondary" href="{{ route('clientes.reservas.pdf', $cliente) }}">PDF reservas</a>
        <a class="btn secondary" href="{{ route('clientes.index') }}">Volver</a>
        @can('editar clientes')<a class="btn" href="{{ route('clientes.edit', $cliente) }}">Editar</a>@endcan
    </div>
</div>

<div class="grid stats" style="margin-top:18px;">
    <div class="card metric"><strong>Total vendido</strong><div class="stat-value">{{ number_format($cliente->ventas->where('estado', '!=', 'anulada')->sum('precio_final'), 2) }}</div></div>
    <div class="card metric"><strong>Total pagado en cuotas</strong><div class="stat-value">{{ number_format($cliente->ventas->flatMap->cuotas->sum('monto_pagado'), 2) }}</div></div>
    <div class="card metric"><strong>Saldo pendiente</strong><div class="stat-value">{{ number_format($cliente->ventas->flatMap->cuotas->sum('saldo_pendiente'), 2) }}</div></div>
</div>

<div class="card">
    <h2>{{ $cliente->nombre }}</h2>
    <p><strong>Documento:</strong> {{ $cliente->documento }}</p>
    <p><strong>Telefono:</strong> @include('clientes.partials.whatsapp-link', ['cliente' => $cliente])</p>
    <p><strong>Email:</strong> {{ $cliente->email }}</p>
    <p><strong>Direccion:</strong> {{ $cliente->direccion }}</p>
    <p><strong>Urbanizacion:</strong> {{ $cliente->urbanizacion?->nombre }}</p>
    <p><strong>Registrado por:</strong> {{ $cliente->createdBy?->name ?? 'Usuario no registrado' }}</p>
    <p><strong>Fecha de registro:</strong> {{ $cliente->created_at?->format('d/m/Y H:i') }}</p>
</div>

<div class="card" style="margin-top:18px;">
    <h2>Reservas</h2>
    <table class="table">
        <thead><tr><th>Lote</th><th>Estado</th><th>Fecha reserva</th></tr></thead>
        <tbody>
        @forelse($cliente->reservas as $reserva)
            <tr><td>{{ $reserva->lote->manzano->codigo }}-{{ $reserva->lote->codigo }}</td><td><span class="badge {{ $reserva->estado }}">{{ $reserva->estado }}</span></td><td>{{ $reserva->fecha_reserva?->format('d/m/Y') }}</td></tr>
        @empty
            <tr><td colspan="3">Sin reservas registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:18px;">
    <h2>Ventas</h2>
    <table class="table">
        <thead><tr><th>Lote</th><th>Precio</th><th>Estado</th></tr></thead>
        <tbody>
        @forelse($cliente->ventas as $venta)
            <tr><td>{{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td><td>{{ number_format($venta->precio_final, 2) }}</td><td><span class="badge {{ $venta->estado }}">{{ $venta->estado }}</span></td></tr>
        @empty
            <tr><td colspan="3">Sin ventas registradas.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
