@extends('layouts.app')
@section('content')
<div class="topbar">
    <div>
        <p class="context-title">Urbanización: {{ $urbanizacion->nombre }} / Lotes</p>
        <h1 class="title">Lotes</h1>
    </div>
    <div class="actions">
        @can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'lotes') }}">Exportar CSV</a>@endcan
        @can('crear lotes')<a class="btn secondary" href="{{ route('lotes.import.create') }}">Importar</a><a class="btn" href="{{ route('lotes.create') }}">Nuevo</a>@endcan
    </div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<form class="filter-form card" method="GET" action="{{ route('lotes.index') }}">
    <div class="field"><label>Buscar lote/código</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Ej. L-15"></div>
    <div class="field"><label>Manzano</label><select name="manzano_id"><option value="">Todos</option>@foreach($manzanos as $manzano)<option value="{{ $manzano->id }}" @selected((string) request('manzano_id') === (string) $manzano->id)>{{ $manzano->codigo }} {{ $manzano->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach($estados as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
    <div class="field"><label>Superficie desde</label><input type="number" step="0.01" min="0" name="superficie_desde" value="{{ request('superficie_desde') }}"></div>
    <div class="field"><label>Superficie hasta</label><input type="number" step="0.01" min="0" name="superficie_hasta" value="{{ request('superficie_hasta') }}"></div>
    <div class="field"><label>Precio desde</label><input type="number" step="0.01" min="0" name="precio_desde" value="{{ request('precio_desde') }}"></div>
    <div class="field"><label>Precio hasta</label><input type="number" step="0.01" min="0" name="precio_hasta" value="{{ request('precio_hasta') }}"></div>
    <div class="filter-actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('lotes.index') }}">Limpiar</a></div>
</form>

<div class="table-scroll">
    <table class="table">
        <thead><tr><th>Lote</th><th>Manzano</th><th>Superficie</th><th>Precio total</th><th>Cuota inicial</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($lotes as $lote)
            <tr>
                <td>{{ $lote->codigo }}</td>
                <td>{{ $lote->manzano->codigo }}</td>
                <td>{{ number_format($lote->superficie, 2) }}</td>
                <td>{{ number_format($lote->precio, 2) }}</td>
                <td>{{ $lote->cuotaInicialTexto() }}</td>
                <td><span class="badge {{ $lote->estado }}">{{ $lote->estado }}</span></td>
                <td class="actions">@can('editar lotes')<a class="btn secondary" href="{{ route('lotes.edit', $lote) }}">Editar</a>@endcan @can('eliminar lotes')<form method="POST" action="{{ route('lotes.destroy', $lote) }}" onsubmit="return confirm('Confirma eliminar este lote?');">@csrf @method('DELETE')<button class="btn danger" type="submit">Eliminar</button></form>@endcan</td>
            </tr>
        @empty
            <tr><td colspan="7">No se encontraron lotes con los filtros seleccionados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($lotes->hasPages())
    <div class="pagination-wrapper">
        {{ $lotes->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
