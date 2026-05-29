@extends('layouts.app')
@section('content')
<div class="topbar">
    <div>
        <h1 class="title">Cambiar contrasena</h1>
        <p class="muted">Por seguridad debes definir una nueva contrasena antes de continuar.</p>
    </div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif

<form class="form card" method="POST" action="{{ route('password.change.update') }}">
    @csrf
    <div class="field"><label>Nueva contrasena</label><input type="password" name="password" required></div>
    <div class="field"><label>Confirmar contrasena</label><input type="password" name="password_confirmation" required></div>
    <div class="field full"><button class="btn">Actualizar contrasena</button></div>
</form>
@endsection
