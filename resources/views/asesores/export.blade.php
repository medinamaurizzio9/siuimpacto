<meta charset="utf-8">
<h2>Asesores comerciales</h2>
<table border="1">
    <thead><tr><th>Asesor</th><th>CI</th><th>Celular</th><th>Email</th><th>Supervisor</th><th>Grupo</th><th>Urbanizaciones</th><th>Estado</th></tr></thead>
    <tbody>@foreach($asesores as $asesor)<tr><td>{{ $asesor->nombre }} {{ $asesor->apellido }}</td><td>{{ $asesor->ci }}</td><td>{{ $asesor->celular }}</td><td>{{ $asesor->email }}</td><td>{{ $asesor->supervisor?->name }}</td><td>{{ $asesor->grupo?->nombre }}</td><td>{{ $asesor->user->urbanizacionesAsignadas->pluck('nombre')->join(', ') }}</td><td>{{ $asesor->activo ? 'activo' : 'inactivo' }}</td></tr>@endforeach</tbody>
</table>
