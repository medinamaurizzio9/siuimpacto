@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Reservas</h1><div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'reservas') }}">Exportar CSV</a>@endcan @can('crear reservas')<a class="btn" href="{{ route('reservas.create') }}">Nueva</a>@endcan</div></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table"><thead><tr><th>Cliente</th><th>Lote</th><th>Reserva</th><th>Vence</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach($reservas as $reserva)
<tr>
<td>{{ $reserva->cliente->nombre }}</td>
<td>{{ $reserva->lote->manzano->codigo }}-{{ $reserva->lote->codigo }}</td>
<td>{{ $reserva->fecha_reserva->format('d/m/Y') }}</td>
<td>{{ $reserva->fecha_vencimiento->format('d/m/Y') }}</td>
<td>{{ number_format($reserva->monto_reserva, 2) }}</td>
<td><span class="badge {{ $reserva->estado }}">{{ $reserva->estado }}</span></td>
<td class="actions"><a class="btn secondary" href="{{ route('clientes.show', $reserva->cliente) }}">Ver detalle</a>@can('editar reservas')<a class="btn secondary" href="{{ route('reservas.edit', $reserva) }}">Editar</a>@endcan @can('cancelar reservas')<form method="POST" action="{{ route('reservas.expire', $reserva) }}" onsubmit="return confirm('Confirma marcar esta reserva como vencida?');">@csrf<button class="btn secondary">Vencer</button></form><form method="POST" action="{{ route('reservas.destroy', $reserva) }}" onsubmit="const m = prompt('Motivo obligatorio de cancelacion'); if(!m) return false; this.motivo.value=m; return confirm('Confirma cancelar esta reserva y liberar el lote?');">@csrf @method('DELETE')<input type="hidden" name="motivo"><button class="btn danger">Cancelar</button></form>@endcan</td>
</tr>
@endforeach
</tbody></table><div class="pagination">{{ $reservas->links() }}</div>
@endsection
