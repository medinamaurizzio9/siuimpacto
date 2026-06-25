@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Grupos comerciales</h1>
    <div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('grupos-comerciales.excel') }}">Exportar Excel</a><a class="btn secondary" href="{{ route('grupos-comerciales.pdf') }}">Exportar PDF</a>@endcan @can('administrar usuarios')<a class="btn" href="{{ route('grupos-comerciales.create') }}">Crear grupo</a>@endcan</div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<form class="card form" method="GET" style="margin-bottom:18px;">
    <div class="field"><label>Buscar grupo</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Nombre del grupo"></div>
    @unless(auth()->user()?->hasRole('supervisor'))
        <div class="field"><label>Supervisor</label><select name="supervisor_id"><option value="">Todos</option>@foreach($supervisores as $supervisor)<option value="{{ $supervisor->id }}" @selected((int) request('supervisor_id') === $supervisor->id)>{{ $supervisor->name }}</option>@endforeach</select></div>
    @endunless
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option><option value="activo" @selected(request('estado') === 'activo')>Activo</option><option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivo</option></select></div>
    <div class="field"><label>&nbsp;</label><button type="submit" class="btn">Filtrar</button></div>
    <div class="field"><label>&nbsp;</label><a class="btn secondary" href="{{ route('grupos-comerciales.index') }}">Limpiar</a></div>
</form>
<table class="table">
    <thead><tr><th>Grupo</th><th>Supervisor</th><th>Asesores</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($grupos as $grupo)
        <tr>
            <td>{{ $grupo->nombre }}<br><span class="muted">{{ $grupo->descripcion }}</span></td>
            <td>{{ $grupo->supervisor?->name ?? 'Sin supervisor' }}</td>
            <td>{{ $grupo->asesores->count() }}</td>
            <td><span class="badge {{ $grupo->activo ? 'activa' : 'cancelada' }}">{{ $grupo->activo ? 'activo' : 'inactivo' }}</span></td>
            <td class="actions">@can('administrar usuarios')<a class="btn secondary" href="{{ route('grupos-comerciales.edit', $grupo) }}">Editar</a>@if($grupo->activo)<form method="POST" action="{{ route('grupos-comerciales.destroy', $grupo) }}" onsubmit="return confirm('Confirma desactivar este grupo?');">@csrf @method('DELETE')<button class="btn danger">Desactivar</button></form>@endif @endcan</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="pagination">{{ $grupos->appends(request()->query())->links() }}</div>
@endsection
