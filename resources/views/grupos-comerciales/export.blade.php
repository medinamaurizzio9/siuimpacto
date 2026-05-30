<meta charset="utf-8">
<h2>Grupos comerciales</h2>
<table border="1">
    <thead><tr><th>Grupo</th><th>Descripcion</th><th>Supervisor</th><th>Asesores</th><th>Estado</th></tr></thead>
    <tbody>@foreach($grupos as $grupo)<tr><td>{{ $grupo->nombre }}</td><td>{{ $grupo->descripcion }}</td><td>{{ $grupo->supervisor?->name }}</td><td>{{ $grupo->asesores->count() }}</td><td>{{ $grupo->activo ? 'activo' : 'inactivo' }}</td></tr>@endforeach</tbody>
</table>
