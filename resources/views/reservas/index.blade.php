@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Reservas</h1><div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'reservas') }}">Exportar CSV</a>@endcan @can('crear reservas')<a class="btn" href="{{ route('reservas.create') }}">Nueva</a>@endcan</div></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<form class="card form" method="GET" style="margin-bottom:18px;">
<div class="field"><label>Cliente</label><input name="cliente" value="{{ request('cliente') }}" placeholder="Nombre/interesado"></div>
<div class="field"><label>Documento</label><input name="documento" value="{{ request('documento') }}" placeholder="Carnet/documento"></div>
<div class="field"><label>Lote</label><input name="lote" value="{{ request('lote') }}"></div>
<div class="field"><label>Manzano</label><input name="manzano" value="{{ request('manzano') }}"></div>
<div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach(['activa','vencida','cancelada','convertida'] as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
<div class="field"><label>Tipo operacion</label><select name="tipo_operacion"><option value="">Todos</option>@foreach($tiposOperacion as $tipo)<option value="{{ $tipo }}" @selected(request('tipo_operacion') === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
@if(auth()->user()->hasAnyRole(['administrador','gerente','supervisor']))
<div class="field"><label>Asesor</label><select name="usuario_id"><option value="">Todos</option>@foreach($vendedores as $vendedor)<option value="{{ $vendedor->id }}" @selected((int) request('usuario_id') === $vendedor->id)>{{ $vendedor->name }}</option>@endforeach</select></div>
@endif
<div class="field"><label>Desde</label><input type="date" name="desde" value="{{ request('desde') }}"></div>
<div class="field"><label>Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}"></div>
<div class="field"><label>&nbsp;</label><button class="btn">Filtrar</button></div>
</form>
<table class="table"><thead><tr><th>Cliente</th><th>Documento</th><th>Lote</th><th>Tipo</th><th>Reserva</th><th>Vence</th><th>Monto</th><th>Estado</th><th>Asesor</th><th></th></tr></thead><tbody>
@foreach($reservas as $reserva)
<tr>
<td>{{ $reserva->cliente->nombre }}</td>
<td>{{ $reserva->cliente->documento }}</td>
<td>{{ $reserva->lote->manzano->codigo }}-{{ $reserva->lote->codigo }}</td>
<td>{{ $reserva->tipo_operacion }}</td>
<td>{{ $reserva->fecha_reserva->format('d/m/Y') }}</td>
<td>{{ $reserva->fecha_vencimiento->format('d/m/Y') }}</td>
<td>{{ number_format($reserva->monto_reserva, 2) }}</td>
<td><span class="badge {{ $reserva->estado }}">{{ $reserva->estado }}</span></td>
<td>{{ $reserva->usuario?->name }}</td>
<td class="actions"><a class="btn secondary" href="{{ route('clientes.show', $reserva->cliente) }}">Ver detalle</a>@can('ver recibo reserva')@if($reserva->cashMovements->contains(fn($movimiento) => $movimiento->concepto === 'reserva' && $movimiento->estado !== 'anulado'))<a class="btn secondary" href="{{ route('reservas.recibo', $reserva) }}" target="_blank" rel="noopener">Recibo PDF</a>@endif @endcan @can('editar reservas')<a class="btn secondary" href="{{ route('reservas.edit', $reserva) }}">Editar</a>@endcan @can('cancelar reservas')<form method="POST" action="{{ route('reservas.expire', $reserva) }}" onsubmit="return confirm('Confirma marcar esta reserva como vencida?');">@csrf<button class="btn secondary">Vencer</button></form><form method="POST" action="{{ route('reservas.destroy', $reserva) }}" onsubmit="const m = prompt('Motivo obligatorio de cancelacion'); if(!m) return false; this.motivo.value=m; return confirm('Confirma cancelar esta reserva y liberar el lote?');">@csrf @method('DELETE')<input type="hidden" name="motivo"><button class="btn danger">Cancelar</button></form>@endcan</td>
</tr>
@endforeach
</tbody></table>
@if ($reservas->hasPages())
<div class="pagination-wrapper">
    {{ $reservas->appends(request()->query())->links() }}
</div>
@endif
@endsection
