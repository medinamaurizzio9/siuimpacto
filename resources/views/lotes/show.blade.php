@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Detalle de lote {{ $lote->manzano->codigo }}-{{ $lote->codigo }}</h1><a class="btn secondary" href="{{ route('mapa') }}">Volver al mapa</a></div>
<div class="card">
    <p><strong>Urbanizacion:</strong> {{ $lote->manzano->urbanizacion->nombre }}</p>
    <p><strong>Manzano:</strong> {{ $lote->manzano->codigo }}</p>
    <p><strong>Lote:</strong> {{ $lote->codigo }}</p>
    <p><strong>Superficie:</strong> {{ number_format($lote->superficie, 2) }} m2</p>
    <p><strong>Precio:</strong> {{ number_format($lote->precio, 2) }}</p>
    <p><strong>Estado:</strong> <span class="badge {{ $lote->estado }}">{{ $lote->estado }}</span></p>
    @if($lote->reservaActiva)
        <p><strong>Reserva activa:</strong> {{ $lote->reservaActiva->cliente->nombre }}</p>
    @endif
    @if($lote->venta)
        <p><strong>Venta:</strong> {{ $lote->venta->cliente->nombre }}</p>
    @endif
    @if($lote->observaciones)
        <p><strong>Observaciones:</strong> {{ $lote->observaciones }}</p>
    @endif
</div>
@endsection
