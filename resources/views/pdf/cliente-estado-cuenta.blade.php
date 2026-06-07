<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#17202a}.header{display:table;width:100%;border-bottom:3px solid {{ $settings['primary_color'] ?? '#0f766e' }};padding-bottom:10px;margin-bottom:16px}.logo{display:table-cell;width:85px}.logo img{max-width:75px;max-height:70px}.company{display:table-cell;vertical-align:top}.system-name{font-size:18px;font-weight:bold}.muted{color:#59666c}.title{font-size:16px;margin:14px 0 8px}.table{width:100%;border-collapse:collapse}.table th,.table td{border:1px solid #d7dee2;padding:5px}.table th{background:#eef3f4}.metrics{display:table;width:100%;margin:12px 0}.metric{display:table-cell;border:1px solid #d7dee2;padding:8px}.footer{border-top:1px solid #ddd;margin-top:18px;padding-top:8px;text-align:center;color:#555}
</style></head><body>
@include('pdf.partials.client-header')
<h1 class="title">Estado de cuenta - {{ $cliente->nombre }}</h1>
<div class="metrics"><div class="metric"><strong>Total vendido</strong><br>{{ number_format($resumen['total_vendido'], 2) }}</div><div class="metric"><strong>Total pagado</strong><br>{{ number_format($resumen['total_pagado'], 2) }}</div><div class="metric"><strong>Total pendiente</strong><br>{{ number_format($resumen['total_pendiente'], 2) }}</div></div>
<h2 class="title">Ventas y cuotas</h2>
<table class="table">
    <thead><tr><th>Venta</th><th>Lote</th><th>Cuota</th><th>Vencimiento</th><th>Monto</th><th>Pagado</th><th>Saldo</th><th>Estado</th></tr></thead>
    <tbody>
    @forelse($cliente->ventas as $venta)
        @if($venta->cuotas->isEmpty())
            <tr><td>#{{ $venta->id }}</td><td>{{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td><td colspan="6">Venta sin cuotas.</td></tr>
        @else
            @foreach($venta->cuotas as $cuota)
                <tr>
                    <td>#{{ $venta->id }}</td>
                    <td>{{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td>
                    <td>{{ $cuota->numero }}</td>
                    <td>{{ optional($cuota->fecha_vencimiento ?? $cuota->fecha_programada)->format('d/m/Y') }}</td>
                    <td>{{ number_format($cuota->monto, 2) }}</td>
                    <td>{{ number_format($cuota->monto_pagado, 2) }}</td>
                    <td>{{ number_format($cuota->saldo_pendiente, 2) }}</td>
                    <td>{{ $cuota->estado }}</td>
                </tr>
            @endforeach
        @endif
    @empty
        <tr><td colspan="8">Sin ventas.</td></tr>
    @endforelse
    </tbody>
</table>
<h2 class="title">Pagos registrados</h2><table class="table"><thead><tr><th>Fecha</th><th>Concepto</th><th>Metodo</th><th>Referencia</th><th>Monto</th><th>Estado</th></tr></thead><tbody>@forelse($cliente->cashMovements as $pago)<tr><td>{{ $pago->fecha?->format('d/m/Y') }}</td><td>{{ $pago->concepto }}</td><td>{{ $pago->metodo_pago }}</td><td>{{ $pago->referencia }}</td><td>{{ number_format($pago->monto, 2) }}</td><td>{{ $pago->estado }}</td></tr>@empty<tr><td colspan="6">Sin pagos registrados.</td></tr>@endforelse</tbody></table>
<div class="footer">{{ $settings['footer_text'] ?? '' }}</div></body></html>
