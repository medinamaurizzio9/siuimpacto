@extends('layouts.app')

@section('content')
<div class="topbar">
    <h1 class="title">Nuevo usuario</h1>
    <div class="actions">
        <a href="{{ route('admin.usuarios') }}" class="btn secondary">Volver</a>
    </div>
</div>

<form class="card form" method="POST" action="{{ route('admin.usuarios.store') }}">
    @csrf
    @include('administracion.usuarios._form')

    <div class="field full">
        <button class="btn" type="submit">Guardar usuario</button>
    </div>
</form>
@endsection
