@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $manzano->exists ? 'Editar manzano' : 'Nuevo manzano' }}</h1><a class="btn secondary" href="{{ route('manzanos.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ $manzano->exists ? route('manzanos.update', $manzano) : route('manzanos.store') }}">
@csrf @if($manzano->exists) @method('PUT') @endif
<div class="field"><label>Urbanizacion</label><select name="urbanizacion_id">@foreach($urbanizaciones as $urbanizacion)<option value="{{ $urbanizacion->id }}" @selected(old('urbanizacion_id', $manzano->urbanizacion_id) == $urbanizacion->id)>{{ $urbanizacion->nombre }}</option>@endforeach</select></div>
<div class="field"><label>Codigo</label><input name="codigo" value="{{ old('codigo', $manzano->codigo) }}" required></div>
<div class="field"><label>Nombre</label><input name="nombre" value="{{ old('nombre', $manzano->nombre) }}"></div>
<div class="field"><label>Orden</label><input name="orden" type="number" value="{{ old('orden', $manzano->orden ?? 0) }}"></div>
<div class="field full"><button class="btn">Guardar</button></div>
</form>
@endsection
