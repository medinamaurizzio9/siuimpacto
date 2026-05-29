@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Clientes</h1><div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'clientes') }}">Exportar CSV</a>@endcan @can('crear clientes')<a class="btn" href="{{ route('clientes.create') }}">Nuevo</a>@endcan</div></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table"><thead><tr><th>Nombre</th><th>Documento</th><th>Telefono</th><th>Email</th><th>Ventas</th><th></th></tr></thead><tbody>
@foreach ($clientes as $cliente)
<tr><td>{{ $cliente->nombre }}</td><td>{{ $cliente->documento }}</td><td>{{ $cliente->telefono }}</td><td>{{ $cliente->email }}</td><td>{{ $cliente->ventas_count }}</td><td class="actions"><a class="btn secondary" href="{{ route('clientes.show', $cliente) }}">Ver</a> @can('editar clientes')<a class="btn secondary" href="{{ route('clientes.edit', $cliente) }}">Editar</a>@endcan @can('eliminar clientes')<form method="POST" action="{{ route('clientes.destroy', $cliente) }}" onsubmit="return confirm('Confirma eliminar este cliente?');">@csrf @method('DELETE')<button class="btn danger">Eliminar</button></form>@endcan</td></tr>
@endforeach
</tbody></table><div class="pagination">{{ $clientes->links() }}</div>
@endsection
