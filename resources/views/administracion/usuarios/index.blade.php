@extends('layouts.app')

@section('content')
<div class="topbar">
    <h1 class="title">Usuarios</h1>
    <div class="actions">
        <a href="{{ route('admin.usuarios.create') }}" class="btn">Nuevo usuario</a>
        <a href="{{ route('admin.usuarios.import') }}" class="btn secondary">Importar Excel</a>
        <a href="{{ route('admin.usuarios.export') }}" class="btn secondary">Exportar Excel</a>
        <a href="{{ route('admin.usuarios.template') }}" class="btn secondary">Descargar plantilla</a>
    </div>
</div>

@if (session('status'))
    <div class="status">{{ session('status') }}</div>
@endif

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th><x-sort-link field="id">ID</x-sort-link></th>
                <th><x-sort-link field="name">Nombre</x-sort-link></th>
                <th><x-sort-link field="email">Email</x-sort-link></th>
                <th><x-sort-link field="rol">Rol</x-sort-link></th>
                <th>Estado</th>
                <th>Cambio obligatorio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</td>
                    <td>{{ ucfirst($user->estado ?? 'activo') }}</td>
                    <td>{{ $user->must_change_password ? 'Si' : 'No' }}</td>
                    <td class="actions">
                        <a href="{{ route('admin.usuarios.edit', $user) }}" class="btn secondary">Editar</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No hay usuarios registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div class="pagination-wrapper">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
