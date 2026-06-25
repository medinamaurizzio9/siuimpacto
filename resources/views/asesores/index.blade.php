@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Equipo comercial</h1>
    <div class="actions">
        @if(auth()->user()?->hasRole('administrador'))
            <a class="btn secondary" href="{{ route('asesores.import') }}">Importar Excel</a>
            <a class="btn secondary" href="{{ route('asesores.template') }}">Descargar plantilla</a>
        @endif
        @can('exportar reportes')<a class="btn secondary" href="{{ route('asesores.excel') }}">Exportar Excel</a><a class="btn secondary" href="{{ route('asesores.pdf') }}">Exportar PDF</a>@endcan
        @can('crear asesores')<a class="btn" href="{{ route('asesores.create') }}">Crear asesor</a>@endcan
    </div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<form class="card form" method="GET" style="margin-bottom:18px;">
    <div class="field"><label>Buscar</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, CI o email"></div>
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option><option value="activo" @selected(request('estado') === 'activo')>Activo</option><option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option></select></div>
    <div class="field"><label>Grupo comercial</label><select name="grupo_comercial_id"><option value="">Todos</option>@foreach($grupos as $grupo)<option value="{{ $grupo->id }}" @selected((int) request('grupo_comercial_id') === $grupo->id)>{{ $grupo->nombre }}</option>@endforeach</select></div>
    @unless(auth()->user()?->hasRole('supervisor'))
        <div class="field"><label>Supervisor</label><select name="supervisor_id"><option value="">Todos</option>@foreach($supervisores as $supervisor)<option value="{{ $supervisor->id }}" @selected((int) request('supervisor_id') === $supervisor->id)>{{ $supervisor->name }}</option>@endforeach</select></div>
    @endunless
    <div class="field"><label>Urbanizacion asignada</label><select name="urbanizacion_id"><option value="">Todas</option>@foreach($urbanizaciones as $urbanizacion)<option value="{{ $urbanizacion->id }}" @selected((int) request('urbanizacion_id') === $urbanizacion->id)>{{ $urbanizacion->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>&nbsp;</label><button type="submit" class="btn">Filtrar</button></div>
    <div class="field"><label>&nbsp;</label><a class="btn secondary" href="{{ route('asesores.index') }}">Limpiar</a></div>
</form>

<table class="table">
    <thead><tr><th>Asesor</th><th>CI</th><th>Celular</th><th>Supervisor</th><th>Grupo</th><th>Urbanizaciones</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($asesores as $asesor)
        <tr>
            <td>{{ $asesor->nombre }} {{ $asesor->apellido }}<br><span class="muted">{{ $asesor->email }}</span></td>
            <td>{{ $asesor->ci }}</td>
            <td>{{ $asesor->celular }}</td>
            <td>{{ $asesor->supervisor?->name ?? 'Sin supervisor' }}</td>
            <td>{{ $asesor->grupo?->nombre ?? $asesor->grupo_comercial ?? 'Sin grupo' }}</td>
            <td>{{ $asesor->user->urbanizacionesAsignadas->pluck('nombre')->join(', ') ?: 'Sin asignar' }}</td>
            <td><span class="badge {{ $asesor->activo ? 'activa' : 'cancelada' }}">{{ $asesor->activo ? 'activo' : 'inactivo' }}</span></td>
            <td class="actions">
                @can('editar asesores')<a class="btn secondary" href="{{ route('asesores.edit', $asesor) }}">Editar</a>@endcan
                @can('resetear contraseña asesor')<form method="POST" action="{{ route('asesores.reset-password', $asesor) }}" onsubmit="return confirm('Confirma resetear la contrasena del asesor a su CI?');">@csrf<button class="btn secondary">Resetear clave</button></form>@endcan
                @if(auth()->user()?->hasRole('administrador'))
                    @php
                        $deleteConfirm = $asesor->has_delete_history
                            ? 'Este usuario tiene ventas, reservas o registros asociados. Si lo elimina, podría afectar reportes históricos. Se recomienda desactivarlo en lugar de eliminarlo. ¿Desea continuar?'
                            : '¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.';
                    @endphp
                    <form method="POST" action="{{ route('asesores.destroy', $asesor) }}" onsubmit="return confirm(@js($deleteConfirm));">@csrf @method('DELETE')<button type="submit" class="btn danger">Eliminar</button></form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="pagination">{{ $asesores->appends(request()->query())->links() }}</div>
@endsection
