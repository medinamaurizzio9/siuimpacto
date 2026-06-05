@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Grupos comerciales</h1>
    <div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('grupos-comerciales.excel') }}">Exportar Excel</a><a class="btn secondary" href="{{ route('grupos-comerciales.pdf') }}">Exportar PDF</a>@endcan @can('administrar usuarios')<a class="btn" href="{{ route('grupos-comerciales.create') }}">Crear grupo</a>@endcan</div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<div class="table-scroll"><table class="table">
    <thead><tr><th>Grupo / Entidad</th><th>Supervisor comercial</th><th>Urbanizaciones</th><th>Equipo</th><th>Vendidos</th><th>Reservas activas</th><th>Contado</th><th>Crédito</th><th>Monto vendido</th><th>Estado</th><th>Acciones</th></tr></thead>
    <tbody>
    @foreach($grupos as $grupo)
        <tr>
            <td>{{ $grupo->nombre }}<br><span class="muted">{{ $grupo->descripcion }}</span></td>
            <td>{{ $grupo->supervisor?->name ?? 'Sin supervisor' }}</td>
            <td>{{ $grupo->urbanizaciones->pluck('nombre')->join(', ') ?: 'Sin asignar' }}</td>
            <td>{{ $grupo->supervisores_ventas_count }} supervisores / {{ $grupo->asesores_count }} vendedores</td>
            <td>{{ $grupo->terrenos_vendidos_count }}</td><td>{{ $grupo->reservas_activas_count }}</td><td>{{ $grupo->ventas_contado_count }}</td><td>{{ $grupo->ventas_credito_count }}</td><td>{{ number_format($grupo->monto_vendido ?? 0, 2) }}</td>
            <td><span class="badge {{ $grupo->activo ? 'activa' : 'cancelada' }}">{{ $grupo->activo ? 'activo' : 'inactivo' }}</span></td>
            <td class="actions"><a class="btn secondary" href="{{ route('grupos-comerciales.show', $grupo) }}">Ver detalle</a>@can('administrar usuarios')<a class="btn secondary" href="{{ route('grupos-comerciales.edit', $grupo) }}">Editar</a>@if(auth()->user()->hasRole('super administrador'))<a class="btn secondary" href="{{ route('grupos-comerciales.asignaciones', $grupo) }}">Asignar urbanizaciones</a>@endif @if($grupo->activo)<form method="POST" action="{{ route('grupos-comerciales.destroy', $grupo) }}" onsubmit="return confirm('Confirma desactivar este grupo?');">@csrf @method('DELETE')<button class="btn danger">Desactivar</button></form>@endif @endcan</td>
        </tr>
    @endforeach
    </tbody>
</table></div>
<div class="pagination">{{ $grupos->links() }}</div>
@endsection
