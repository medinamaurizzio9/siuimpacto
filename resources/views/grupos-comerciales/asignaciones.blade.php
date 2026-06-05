@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Asignar urbanizaciones: {{ $grupo->nombre }}</h1><a class="btn secondary" href="{{ route('grupos-comerciales.show', $grupo) }}">Volver</a></div>
<form class="card form" method="POST" action="{{ route('grupos-comerciales.asignaciones.update', $grupo) }}">@csrf @method('PUT')<div class="field full"><label>Urbanizaciones permitidas</label><div class="check-list">@foreach($urbanizaciones as $urbanizacion)<label class="check-row compact"><input type="checkbox" name="urbanizaciones[]" value="{{ $urbanizacion->id }}" @checked(in_array($urbanizacion->id,$asignadas,true))> {{ $urbanizacion->nombre }}</label>@endforeach</div></div><div class="field full"><button class="btn" type="submit">Guardar asignaciones</button></div></form>
@endsection
