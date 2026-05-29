@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $cliente->exists ? 'Editar cliente' : 'Nuevo cliente' }}</h1><a class="btn secondary" href="{{ route('clientes.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
@if (session('duplicate_cliente_data'))
    @php
        $duplicate = session('duplicate_cliente_data');
        $duplicateLoteId = session('duplicate_lote_id') ?: old('lote_id', $loteId ?? null);
        $useUrl = $duplicateLoteId
            ? route('reservas.create', ['cliente_id' => $duplicate['id'], 'lote_id' => $duplicateLoteId])
            : route('clientes.show', $duplicate['id']);
    @endphp
    <div class="errors">
        <h2 style="margin-top:0;">Cliente ya registrado.</h2>
        <p>{{ session('duplicate_cliente_message') }}</p>
        <p><strong>Nombre:</strong> {{ $duplicate['nombre'] }}</p>
        <p><strong>Carnet/Documento:</strong> {{ $duplicate['documento'] }}</p>
        <p><strong>Registrado por:</strong> {{ $duplicate['created_by'] }}</p>
        <p><strong>Fecha:</strong> {{ $duplicate['created_at'] }}</p>
        <p>No es necesario volver a registrarlo.</p>
        <div class="actions">
            <a class="btn" href="{{ $useUrl }}">Usar cliente existente</a>
            <a class="btn secondary" href="{{ route('clientes.show', $duplicate['id']) }}">Ver cliente</a>
        </div>
    </div>
@endif
<form class="form card" method="POST" action="{{ $cliente->exists ? route('clientes.update', $cliente) : route('clientes.store') }}">
@csrf @if($cliente->exists) @method('PUT') @endif
@if(! $cliente->exists && old('lote_id', $loteId ?? null))
    <input type="hidden" name="lote_id" value="{{ old('lote_id', $loteId ?? null) }}">
@endif
<div class="field"><label>Nombre</label><input name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required></div>
<div class="field"><label>Documento</label><input name="documento" value="{{ old('documento', $cliente->documento) }}"></div>
<div class="field"><label>Telefono</label><input name="telefono" value="{{ old('telefono', $cliente->telefono) }}"></div>
<div class="field"><label>Email</label><input name="email" type="email" value="{{ old('email', $cliente->email) }}"></div>
<div class="field full"><label>Direccion</label><textarea name="direccion">{{ old('direccion', $cliente->direccion) }}</textarea></div>
<div class="field full"><button class="btn">Guardar</button></div>
</form>
@endsection
