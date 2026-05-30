<meta charset="utf-8">
<h2>Supervisores comerciales</h2>
<table border="1">
    <thead><tr><th>Nombre</th><th>CI</th><th>Celular</th><th>Email</th><th>Direccion</th><th>Estado</th></tr></thead>
    <tbody>@foreach($supervisores as $supervisor)<tr><td>{{ $supervisor->nombre }}</td><td>{{ $supervisor->ci }}</td><td>{{ $supervisor->celular }}</td><td>{{ $supervisor->email }}</td><td>{{ $supervisor->direccion }}</td><td>{{ $supervisor->activo ? 'activo' : 'inactivo' }}</td></tr>@endforeach</tbody>
</table>
