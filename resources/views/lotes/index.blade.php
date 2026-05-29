@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Lotes</h1><div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'lotes') }}">Exportar CSV</a>@endcan @can('crear lotes')<a class="btn secondary" href="{{ route('lotes.import.create') }}">Importar</a><a class="btn" href="{{ route('lotes.create') }}">Nuevo</a>@endcan</div></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table"><thead><tr><th>Lote</th><th>Manzano</th><th>Urbanizacion</th><th>Superficie</th><th>Precio</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach ($lotes as $lote)
<tr><td>{{ $lote->codigo }}</td><td>{{ $lote->manzano->codigo }}</td><td>{{ $lote->manzano->urbanizacion->nombre }}</td><td>{{ number_format($lote->superficie, 2) }}</td><td>{{ number_format($lote->precio, 2) }}</td><td><span class="badge {{ $lote->estado }}">{{ $lote->estado }}</span></td><td class="actions">@can('editar lotes')<a class="btn secondary" href="{{ route('lotes.edit', $lote) }}">Editar</a>@endcan @can('eliminar lotes')<form method="POST" action="{{ route('lotes.destroy', $lote) }}" onsubmit="return confirm('Confirma eliminar este lote?');">@csrf @method('DELETE')<button class="btn danger">Eliminar</button></form>@endcan</td></tr>
@endforeach
</tbody></table><div class="pagination">{{ $lotes->links() }}</div>
@endsection
