@extends('layouts.app')

@section('content')
<h1>Auditoría</h1>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Módulo</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $audit)
                    <tr>
                        <td>{{ $audit->created_at }}</td>
                        <td>{{ $audit->user_id ?? 'Sistema' }}</td>
                        <td>{{ $audit->modulo ?? '-' }}</td>
                        <td>{{ $audit->accion ?? '-' }}</td>
                        <td>{{ $audit->descripcion ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $audits->links() }}
    </div>
</div>
@endsection
