@extends('layouts.app')
@section('content')
<div class="topbar">
    <h1 class="title">Equipo comercial</h1>
    <div class="actions">@can('crear asesores')<a class="btn" href="{{ route('asesores.create') }}">Crear asesor</a>@endcan</div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<table class="table">
    <thead><tr><th>Asesor</th><th>CI</th><th>Celular</th><th>Supervisor</th><th>Urbanizaciones</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    @foreach($asesores as $asesor)
        <tr>
            <td>{{ $asesor->nombre }} {{ $asesor->apellido }}<br><span class="muted">{{ $asesor->email }}</span></td>
            <td>{{ $asesor->ci }}</td>
            <td>{{ $asesor->celular }}</td>
            <td>{{ $asesor->supervisor?->name ?? 'Sin supervisor' }}</td>
            <td>{{ $asesor->user->urbanizacionesAsignadas->pluck('nombre')->join(', ') ?: 'Sin asignar' }}</td>
            <td><span class="badge {{ $asesor->activo ? 'activa' : 'cancelada' }}">{{ $asesor->activo ? 'activo' : 'inactivo' }}</span></td>
            <td class="actions">
                @can('editar asesores')<a class="btn secondary" href="{{ route('asesores.edit', $asesor) }}">Editar</a>@endcan
                @can('resetear contraseña asesor')<form method="POST" action="{{ route('asesores.reset-password', $asesor) }}" onsubmit="return confirm('Confirma resetear la contrasena del asesor a su CI?');">@csrf<button class="btn secondary">Resetear clave</button></form>@endcan
                @can('desactivar asesores')@if($asesor->activo)<form method="POST" action="{{ route('asesores.destroy', $asesor) }}" onsubmit="return confirm('Confirma desactivar este asesor?');">@csrf @method('DELETE')<button class="btn danger">Desactivar</button></form>@endif @endcan
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="pagination">{{ $asesores->links() }}</div>
@endsection
