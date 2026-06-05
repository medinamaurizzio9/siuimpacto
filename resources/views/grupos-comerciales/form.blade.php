@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $grupo->exists ? 'Editar grupo comercial' : 'Crear grupo comercial' }}</h1><a class="btn secondary" href="{{ route('grupos-comerciales.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ $grupo->exists ? route('grupos-comerciales.update', $grupo) : route('grupos-comerciales.store') }}">
    @csrf @if($grupo->exists) @method('PUT') @endif
    <div class="field"><label>Nombre</label><input name="nombre" value="{{ old('nombre', $grupo->nombre) }}" required></div>
    <div class="field"><label>Supervisor</label><select name="supervisor_id"><option value="">Sin supervisor</option>@foreach($supervisores as $supervisor)<option value="{{ $supervisor->id }}" @selected(old('supervisor_id', $grupo->supervisor_id) == $supervisor->id)>{{ $supervisor->name }}</option>@endforeach</select></div>
    <div class="field full"><label>Descripcion</label><textarea name="descripcion">{{ old('descripcion', $grupo->descripcion) }}</textarea></div>
    <div class="field full"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones', $grupo->observaciones) }}</textarea></div>
    <label class="check-row"><input type="checkbox" name="activo" value="1" @checked(old('activo', $grupo->activo ?? true))> Activo</label>
    <div class="field full"><button class="btn" type="submit">Guardar grupo</button></div>
</form>
@endsection
