@extends('layouts.app')
@section('content')
<div class="topbar">
    <div>
        <p class="context-title">Urbanización: {{ $urbanizacion->nombre }} / Manzanos</p>
        <h1 class="title">Manzanos</h1>
    </div>
    @can('crear manzanos')<a class="btn" href="{{ route('manzanos.create') }}">Nuevo</a>@endcan
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<form class="filter-form compact card" method="GET" action="{{ route('manzanos.index') }}">
    <div class="field"><label>Buscar por código o nombre</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Ej. M-A"></div>
    <div class="filter-actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('manzanos.index') }}">Limpiar</a></div>
</form>

<div class="table-scroll">
<table class="table"><thead><tr><th>Código</th><th>Nombre</th><th>Lotes</th><th></th></tr></thead><tbody>
@forelse ($manzanos as $manzano)
<tr><td>{{ $manzano->codigo }}</td><td>{{ $manzano->nombre }}</td><td>{{ $manzano->lotes_count }}</td><td class="actions">@can('editar manzanos')<a class="btn secondary" href="{{ route('manzanos.edit', $manzano) }}">Editar</a>@endcan @can('eliminar manzanos')<form method="POST" action="{{ route('manzanos.destroy', $manzano) }}" onsubmit="return confirm('Confirma eliminar este manzano?');">@csrf @method('DELETE')<button class="btn danger" type="submit">Eliminar</button></form>@endcan</td></tr>
@empty
<tr><td colspan="4">No se encontraron manzanos con los filtros seleccionados.</td></tr>
@endforelse
</tbody></table>
</div>
@if ($manzanos->hasPages())
<div class="pagination-wrapper">
    {{ $manzanos->appends(request()->query())->links() }}
</div>
@endif
@endsection
