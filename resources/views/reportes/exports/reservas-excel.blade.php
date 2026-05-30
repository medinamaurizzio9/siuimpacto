<meta charset="utf-8">
<h2>IMPACTO URBANIZACIONES - Reporte de reservas</h2>
<table border="1">
    <thead><tr><th>Fecha</th><th>Cliente</th><th>Documento</th><th>Manzano</th><th>Lote</th><th>Tipo operacion</th><th>Estado</th><th>Asesor</th><th>Fecha vencimiento</th></tr></thead>
    <tbody>
    @foreach($reservas as $reserva)
        <tr>
            <td>{{ $reserva->fecha_reserva?->format('d/m/Y') }}</td>
            <td>{{ $reserva->cliente->nombre }}</td>
            <td>{{ $reserva->cliente->documento }}</td>
            <td>{{ $reserva->lote->manzano->codigo }}</td>
            <td>{{ $reserva->lote->codigo }}</td>
            <td>{{ $reserva->tipo_operacion }}</td>
            <td>{{ $reserva->estado }}</td>
            <td>{{ $reserva->usuario?->name }}</td>
            <td>{{ $reserva->fecha_vencimiento?->format('d/m/Y') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
