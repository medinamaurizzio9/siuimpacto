@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Asignar urbanizaciones a asesores</h1>
    <a class="btn secondary" href="{{ route('dashboard') }}">Volver</a>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<form class="card form" method="GET" style="margin-bottom:18px;">
    <div class="field"><label>Buscar asesor</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, email o CI"></div>
    <div class="field"><label>Urbanizacion</label><select name="urbanizacion_id"><option value="">Todas</option>@foreach($urbanizaciones as $urbanizacion)<option value="{{ $urbanizacion->id }}" @selected((int) request('urbanizacion_id') === $urbanizacion->id)>{{ $urbanizacion->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option><option value="activo" @selected(request('estado') === 'activo')>Activo</option><option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option></select></div>
    <div class="field check-row compact" style="align-self:end;"><label><input type="checkbox" name="solo_activos" value="1" @checked(request()->boolean('solo_activos'))> Solo asesores activos</label></div>
    <div class="field"><label>&nbsp;</label><button type="submit" class="btn">Filtrar</button></div>
    <div class="field"><label>&nbsp;</label><a class="btn secondary" href="{{ route('urbanizaciones.asignaciones') }}">Limpiar</a></div>
</form>

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
    @if($usuarios->hasPages())
        <div class="pagination-wrapper">{{ $usuarios->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
