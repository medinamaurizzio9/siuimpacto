@extends('layouts.app')
@section('content')
<div class="topbar">
    <div>
        <h1 class="title">Configuracion Urbanizacion GPS</h1>
        <p class="muted">Puntos de referencia GPS configurables por urbanizacion.</p>
    </div>
    <a class="btn" href="{{ route('admin.urbanizacion-gps.create', ['urbanizacion_id' => $urbanizacionId]) }}">Nuevo Punto de Referencia</a>
</div>

@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif

<form method="GET" action="{{ route('admin.urbanizacion-gps.index') }}" class="card form" style="margin-bottom:18px;">
    <div class="field">
        <label>Urbanizacion</label>
        <select name="urbanizacion_id">
            <option value="">Todas</option>
            @foreach($urbanizaciones as $urbanizacion)
                <option value="{{ $urbanizacion->id }}" @selected($urbanizacionId === $urbanizacion->id)>{{ $urbanizacion->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="field"><label>&nbsp;</label><button class="btn" type="submit">Filtrar</button></div>
    <div class="field"><label>&nbsp;</label><a class="btn secondary" href="{{ route('admin.urbanizacion-gps.index') }}">Limpiar</a></div>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Urbanizacion</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Latitud</th>
            <th>Longitud</th>
            <th>Plano</th>
            <th>Descripcion</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse($referencias as $referencia)
            <tr>
                <td>{{ $referencia->urbanizacion?->nombre }}</td>
                <td>{{ $referencia->nombre }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $referencia->tipo_referencia ?? 'otro')) }}</td>
                <td>{{ $referencia->latitud }}</td>
                <td>{{ $referencia->longitud }}</td>
                <td>
                    @if(! is_null($referencia->plano_x) && ! is_null($referencia->plano_y))
                        X: {{ $referencia->plano_x }}% / Y: {{ $referencia->plano_y }}%
                    @else
                        Sin posicion
                    @endif
                </td>
                <td>{{ $referencia->descripcion ?: 'Sin descripcion' }}</td>
                <td><span @class(['badge', 'disponible' => $referencia->activo, 'bloqueado' => ! $referencia->activo])>{{ $referencia->activo ? 'Activo' : 'Inactivo' }}</span></td>
                <td class="actions">
                    <a class="btn secondary" href="{{ route('admin.urbanizacion-gps.edit', $referencia) }}">Editar</a>
                    <form method="POST" action="{{ route('admin.urbanizacion-gps.destroy', $referencia) }}" onsubmit="return confirm('Desea eliminar este punto de referencia GPS?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn danger" type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="9">No existen puntos de referencia GPS registrados.</td></tr>
        @endforelse
    </tbody>
</table>

@if($referencias->hasPages())
    <div class="pagination-wrapper">{{ $referencias->links() }}</div>
@endif
@endsection
