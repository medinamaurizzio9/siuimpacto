@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Asignar urbanizaciones a asesores</h1>
    <a class="btn secondary" href="{{ route('dashboard') }}">Volver</a>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<div class="card">
    <table class="table">
        <thead><tr><th>Asesor</th><th>Urbanizaciones asignadas</th><th></th></tr></thead>
        <tbody>
        @foreach($usuarios as $usuario)
            <tr>
                <td>{{ $usuario->name }}<br><span class="muted">{{ $usuario->email }}</span></td>
                <td>
                    <form method="POST" action="{{ route('urbanizaciones.asignaciones.update', $usuario) }}" id="asignacion-{{ $usuario->id }}">
                        @csrf @method('PUT')
                        <div class="check-list">
                            @foreach($urbanizaciones as $urbanizacion)
                                <label class="check-row compact">
                                    <input type="checkbox" name="urbanizaciones[]" value="{{ $urbanizacion->id }}" @checked($usuario->urbanizacionesAsignadas->contains('id', $urbanizacion->id)) @disabled($soloLectura)>
                                    {{ $urbanizacion->nombre }}
                                </label>
                            @endforeach
                        </div>
                    </form>
                </td>
                <td>
                    @unless($soloLectura)
                        <button class="btn" form="asignacion-{{ $usuario->id }}">Guardar</button>
                    @else
                        <span class="muted">Solo lectura</span>
                    @endunless
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
