@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Urbanizaciones</h1><a class="btn" href="{{ route('urbanizaciones.create') }}">Nueva</a></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table">
    <thead><tr><th>Nombre</th><th>Ubicacion</th><th>Superficie</th><th>Manzanos</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach ($urbanizaciones as $urbanizacion)
        <tr>
            <td>{{ $urbanizacion->nombre }}</td><td>{{ $urbanizacion->ubicacion }}</td><td>{{ number_format($urbanizacion->superficie_total, 2) }}</td><td>{{ $urbanizacion->manzanos_count }}</td><td>{{ $urbanizacion->estado }}</td>
            <td class="actions"><a class="btn secondary" href="{{ route('urbanizaciones.edit', $urbanizacion) }}">Editar</a><form method="POST" action="{{ route('urbanizaciones.destroy', $urbanizacion) }}">@csrf @method('DELETE')<button class="btn danger">Eliminar</button></form></td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="pagination">{{ $urbanizaciones->links() }}</div>
@endsection
