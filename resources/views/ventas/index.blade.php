@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Ventas</h1><div class="actions">@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'ventas') }}">Exportar CSV</a>@endcan @can('crear ventas')<a class="btn" href="{{ route('ventas.create') }}">Nueva</a>@endcan</div></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table"><thead><tr><th>Fecha</th><th>Cliente</th><th>Lote</th><th>Precio</th><th>Cuota inicial</th><th>Saldo</th><th>Cuotas</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach ($ventas as $venta)
<tr><td>{{ $venta->fecha_venta->format('d/m/Y') }}</td><td>{{ $venta->cliente->nombre }}</td><td>{{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td><td>{{ number_format($venta->precio_final, 2) }}</td><td>{{ number_format($venta->cuota_inicial, 2) }}</td><td>{{ number_format($venta->saldo_financiar, 2) }}</td><td>{{ $venta->cuotas->count() }}</td><td><span class="badge {{ $venta->estado }}">{{ $venta->estado }}</span></td><td class="actions">@if(auth()->user()->hasRole('administrador') && auth()->user()->can('editar ventas') && ($venta->estado !== 'anulada' || auth()->user()->can('editar ventas anuladas')))<a class="btn secondary" href="{{ route('ventas.edit', $venta) }}">Editar</a>@endif<a class="btn secondary" href="{{ route('pdf.plan', $venta) }}">Imprimir plan de pagos</a><a class="btn secondary" href="{{ route('pdf.contrato', $venta) }}">Generar contrato</a>@can('anular ventas')<form method="POST" action="{{ route('ventas.destroy', $venta) }}" onsubmit="return confirm('Confirma anular esta venta?');">@csrf @method('DELETE')<input type="hidden" name="motivo" value="Anulacion confirmada desde listado"><button class="btn danger">Anular</button></form>@endcan</td></tr>
@endforeach
</tbody></table><div class="pagination">{{ $ventas->links() }}</div>
@endsection
