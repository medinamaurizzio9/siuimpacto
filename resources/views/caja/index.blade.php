@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Caja</h1>@can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'caja') }}">Exportar CSV</a>@endcan</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table"><thead><tr><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Concepto</th><th>Metodo</th><th>Monto</th><th>Estado</th><th></th></tr></thead><tbody>
@foreach($movimientos as $movimiento)
<tr><td>{{ $movimiento->fecha->format('d/m/Y') }}</td><td>{{ $movimiento->cliente?->nombre }}</td><td>{{ $movimiento->tipo }}</td><td>{{ $movimiento->concepto }}</td><td>{{ $movimiento->metodo_pago }}</td><td>{{ number_format($movimiento->monto, 2) }}</td><td><span class="badge {{ $movimiento->estado }}">{{ $movimiento->estado }}</span></td><td class="actions"><a class="btn secondary" href="{{ route('pdf.recibo', $movimiento) }}">Imprimir recibo</a>@can('anular caja')@if($movimiento->estado !== 'anulado')<form method="POST" action="{{ route('caja.annul', $movimiento) }}" onsubmit="const m = prompt('Motivo obligatorio de anulacion'); if(!m) return false; this.motivo.value=m; return confirm('Confirma que deseas anular este movimiento de caja?');">@csrf<input type="hidden" name="motivo"><button class="btn danger">Anular</button></form>@endif @endcan</td></tr>
@endforeach
</tbody></table><div class="pagination">{{ $movimientos->links() }}</div>
@endsection
