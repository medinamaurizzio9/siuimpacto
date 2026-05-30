@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Configuracion comercial</h1></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ route('admin.configuracion.update') }}">
    @csrf @method('PUT')
    <div class="field">
        <label>Dias habiles de reserva para asesores</label>
        <input type="number" min="1" max="30" name="reserva_dias_habiles_asesor" value="{{ old('reserva_dias_habiles_asesor', $reservaDiasHabilesAsesor) }}" required>
        <span class="muted">No cuenta sabados ni domingos.</span>
    </div>
    <div class="field full"><button class="btn">Guardar configuracion</button></div>
</form>
@endsection
