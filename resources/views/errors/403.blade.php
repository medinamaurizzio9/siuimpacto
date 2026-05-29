@extends('layouts.app')
@section('content')
<div class="card">
    <h1 class="title">No tienes permiso para realizar esta accion</h1>
    <p class="muted">{{ $exception->getMessage() ?: 'Tu rol actual no tiene acceso a esta operacion. Si necesitas continuar, solicita apoyo al administrador o gerente.' }}</p>
    <a class="btn secondary" href="{{ url()->previous() }}">Volver</a>
</div>
@endsection
