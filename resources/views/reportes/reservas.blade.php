@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Reporte de reservas</h1>@can('exportar reportes')<a class="btn secondary" href="{{ route('reportes.csv', ['reporte' => 'reservas'] + request()->query()) }}">Exportar CSV</a>@endcan</div>

<form class="card form" method="GET">
    <div class="field"><label>Desde</label><input type="date" name="desde" value="{{ request('desde') }}"></div>
    <div class="field"><label>Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}"></div>
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach(['activa','vencida','cancelada','convertida'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
    @if(auth()->user()->hasAnyRole(['administrador', 'gerente', 'supervisor']))
        <div class="field"><label>Vendedor/asesor</label><select name="usuario_id"><option value="">Todos</option>@foreach($vendedores as $vendedor)<option value="{{ $vendedor->id }}" @selected((int) request('usuario_id') === $vendedor->id)>{{ $vendedor->name }}</option>@endforeach</select></div>
    @endif
    <div class="field"><label>&nbsp;</label><button class="btn">Filtrar</button></div>
</form>

<div class="grid stats" style="margin-top:18px;">
    <div class="card"><div class="muted">Activas</div><div class="stat-value">{{ $activas }}</div></div>
    <div class="card"><div class="muted">Vencidas</div><div class="stat-value">{{ $vencidas }}</div></div>
    <div class="card"><div class="muted">Proximas a vencer</div><div class="stat-value">{{ $proximas }}</div></div>
    <div class="card"><div class="muted">Convertidas</div><div class="stat-value">{{ $convertidas }}</div></div>
</div>

<div class="card" style="margin-top:18px;" data-report="reservas">
    <h2>Tabla de reservas</h2>
    <table class="table"><thead><tr><th>Cliente/interesado</th><th>Lote</th><th>Manzano</th><th>Estado</th><th>Fecha reserva</th><th>Fecha vencimiento</th><th>Vendedor</th></tr></thead><tbody>
        @forelse($reservas as $reserva)
            <tr><td>{{ $reserva->cliente->nombre }}</td><td>{{ $reserva->lote->codigo }}</td><td>{{ $reserva->lote->manzano->codigo }}</td><td><span class="badge {{ $reserva->estado }}">{{ $reserva->estado }}</span></td><td>{{ $reserva->fecha_reserva?->format('d/m/Y') }}</td><td>{{ $reserva->fecha_vencimiento?->format('d/m/Y') }}</td><td>{{ $reserva->usuario?->name ?? 'Sin asignar' }}</td></tr>
        @empty
            <tr><td colspan="7">No se encontraron reservas con los filtros aplicados.</td></tr>
        @endforelse
    </tbody></table>
</div>
@endsection
