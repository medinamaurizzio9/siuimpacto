@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Cliente</h1>
    <div class="actions">
        <a class="btn secondary" href="{{ route('clientes.index') }}">Volver</a>
        @can('editar clientes')<a class="btn" href="{{ route('clientes.edit', $cliente) }}">Editar</a>@endcan
    </div>
</div>

<div class="card">
    <h2>{{ $cliente->nombre }}</h2>
    <p><strong>Documento:</strong> {{ $cliente->documento }}</p>
    <p><strong>Telefono:</strong> {{ $cliente->telefono }}</p>
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
