@extends('layouts.app')

@section('content')
<h1>Roles y permisos</h1>

<div class="card">
    <div class="card-body">
        <h3>Roles</h3>

        <table class="table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Permisos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>{{ $role->permissions->pluck('name')->join(', ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Permisos registrados</h3>

        <ul>
            @foreach($permissions as $permission)
                <li>{{ $permission->name }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endsection