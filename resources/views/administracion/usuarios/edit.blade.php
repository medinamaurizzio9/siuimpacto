@extends('layouts.app')

@section('content')
<div class="topbar">
    <h1 class="title">Editar usuario</h1>
    <div class="actions">
        <a href="{{ route('admin.usuarios') }}" class="btn secondary">Volver</a>
    </div>
</div>

<form class="card form" method="POST" action="{{ route('admin.usuarios.update', $usuario) }}">
    @csrf
    @method('PUT')
    @include('administracion.usuarios._form')

    <div class="field full">
        <button class="btn" type="submit">Actualizar usuario</button>
    </div>
</form>
@endsection
