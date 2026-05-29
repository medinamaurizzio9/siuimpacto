@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Manzanos</h1><a class="btn" href="{{ route('manzanos.create') }}">Nuevo</a></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table"><thead><tr><th>Codigo</th><th>Nombre</th><th>Urbanizacion</th><th>Lotes</th><th></th></tr></thead><tbody>
@foreach ($manzanos as $manzano)
<tr><td>{{ $manzano->codigo }}</td><td>{{ $manzano->nombre }}</td><td>{{ $manzano->urbanizacion->nombre }}</td><td>{{ $manzano->lotes_count }}</td><td class="actions"><a class="btn secondary" href="{{ route('manzanos.edit', $manzano) }}">Editar</a><form method="POST" action="{{ route('manzanos.destroy', $manzano) }}">@csrf @method('DELETE')<button class="btn danger">Eliminar</button></form></td></tr>
@endforeach
</tbody></table><div class="pagination">{{ $manzanos->links() }}</div>
@endsection
