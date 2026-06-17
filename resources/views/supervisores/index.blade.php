@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Supervisores comerciales</h1>
    <div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('supervisores.excel') }}">Exportar Excel</a><a class="btn secondary" href="{{ route('supervisores.pdf') }}">Exportar PDF</a>@endcan <a class="btn" href="{{ route('supervisores.create') }}">Crear supervisor</a></div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table">
    <thead><tr><th>Supervisor</th><th>CI</th><th>Celular</th><th>Email</th><th>Direccion</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($supervisores as $supervisor)
        <tr>
            <td>{{ $supervisor->nombre }}</td>
            <td>{{ $supervisor->ci }}</td>
            <td>{{ $supervisor->celular }}</td>
            <td>{{ $supervisor->email }}</td>
            <td>{{ $supervisor->direccion }}</td>
            <td><span class="badge {{ $supervisor->activo ? 'activa' : 'cancelada' }}">{{ $supervisor->activo ? 'activo' : 'inactivo' }}</span></td>
            <td class="actions"><a class="btn secondary" href="{{ route('supervisores.edit', $supervisor) }}">Editar</a>@if(auth()->user()?->hasRole('administrador'))@php $deleteConfirm = $supervisor->has_delete_history ? 'Este usuario tiene ventas, reservas o registros asociados. Si lo elimina, podría afectar reportes históricos. Se recomienda desactivarlo en lugar de eliminarlo. ¿Desea continuar?' : '¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer.'; @endphp<form method="POST" action="{{ route('supervisores.destroy', $supervisor) }}" onsubmit="return confirm(@js($deleteConfirm));">@csrf @method('DELETE')<button type="submit" class="btn danger">Eliminar</button></form>@endif</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="pagination">{{ $supervisores->links() }}</div>
@endsection
