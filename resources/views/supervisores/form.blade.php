@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $supervisor->exists ? 'Editar supervisor' : 'Crear supervisor' }}</h1><a class="btn secondary" href="{{ route('supervisores.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<div class="status">La contrasena inicial es el CI del supervisor. Debera cambiarla al iniciar sesion.</div>
<form class="form card" method="POST" action="{{ $supervisor->exists ? route('supervisores.update', $supervisor) : route('supervisores.store') }}">
    @csrf @if($supervisor->exists) @method('PUT') @endif
    <div class="field"><label>Nombre</label><input name="nombre" value="{{ old('nombre', $supervisor->nombre) }}" required></div>
    <div class="field"><label>CI</label><input name="ci" value="{{ old('ci', $supervisor->ci) }}" required></div>
    <div class="field"><label>Celular</label><input name="celular" value="{{ old('celular', $supervisor->celular) }}"></div>
    <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email', $supervisor->email) }}" required></div>
    <div class="field full"><label>Direccion</label><input name="direccion" value="{{ old('direccion', $supervisor->direccion) }}"></div>
    <label class="check-row"><input type="checkbox" name="activo" value="1" @checked(old('activo', $supervisor->activo ?? true))> Activo</label>
    <div class="field full"><button class="btn" type="submit">Guardar supervisor</button></div>
</form>
@endsection
