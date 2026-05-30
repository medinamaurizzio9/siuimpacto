@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">{{ $asesor->exists ? 'Editar asesor' : 'Crear asesor' }}</h1>
    <a class="btn secondary" href="{{ route('asesores.index') }}">Volver</a>
</div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<div class="status">La contrasena inicial es el CI del asesor. Debera cambiarla al iniciar sesion.</div>

<form class="form card" method="POST" action="{{ $asesor->exists ? route('asesores.update', $asesor) : route('asesores.store') }}">
    @csrf @if($asesor->exists) @method('PUT') @endif
    <div class="field"><label>Nombre</label><input name="nombre" value="{{ old('nombre', $asesor->nombre) }}" required></div>
    <div class="field"><label>Apellido</label><input name="apellido" value="{{ old('apellido', $asesor->apellido) }}" required></div>
    <div class="field"><label>CI</label><input name="ci" value="{{ old('ci', $asesor->ci) }}" required></div>
    <div class="field"><label>Celular</label><input name="celular" value="{{ old('celular', $asesor->celular) }}"></div>
    <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email', $asesor->email) }}" required></div>
    <div class="field"><label>Direccion</label><input name="direccion" value="{{ old('direccion', $asesor->direccion) }}"></div>
    <div class="field"><label>Grupo comercial</label><select name="grupo_comercial_id"><option value="">Sin grupo</option>@foreach($grupos as $grupo)<option value="{{ $grupo->id }}" @selected(old('grupo_comercial_id', $asesor->grupo_comercial_id) == $grupo->id)>{{ $grupo->nombre }}</option>@endforeach</select></div>
    <div class="field">
        <label>Supervisor</label>
        @role('supervisor')
            <input value="{{ auth()->user()->name }}" disabled>
            <input type="hidden" name="supervisor_id" value="{{ auth()->id() }}">
        @else
            <select name="supervisor_id">
                <option value="">Sin supervisor</option>
                @foreach($supervisores as $supervisor)
                    <option value="{{ $supervisor->id }}" @selected(old('supervisor_id', $asesor->supervisor_id) == $supervisor->id)>{{ $supervisor->name }}</option>
                @endforeach
            </select>
        @endrole
    </div>
    <label class="check-row"><input type="checkbox" name="activo" value="1" @checked(old('activo', $asesor->activo ?? true))> Activo</label>
    <div class="field full">
        <label>Urbanizaciones asignadas</label>
        <div class="check-list">
            @foreach($urbanizaciones as $urbanizacion)
                <label class="check-row compact">
                    <input type="checkbox" name="urbanizaciones[]" value="{{ $urbanizacion->id }}" @checked(in_array($urbanizacion->id, old('urbanizaciones', $urbanizacionesAsignadas), true))>
                    {{ $urbanizacion->nombre }}
                </label>
            @endforeach
        </div>
    </div>
    <div class="field full"><button class="btn">Guardar asesor</button></div>
</form>
@endsection
