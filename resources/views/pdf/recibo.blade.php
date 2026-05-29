<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111}.header{border-bottom:2px solid #111;padding-bottom:10px;margin-bottom:18px}.title{font-size:18px;font-weight:bold}.table{width:100%;border-collapse:collapse}.table td{border:1px solid #ccc;padding:8px}</style></head><body>
<div class="header"><div class="title">IMPACTO URBANIZACIONES - Sistema Integral de Terrenos</div><div>Recibo de pago Nro. {{ str_pad((string) $movimiento->id, 8, '0', STR_PAD_LEFT) }}</div></div>
<table class="table">
<tr><td>Fecha</td><td>{{ $movimiento->fecha->format('d/m/Y') }}</td></tr>
<tr><td>Cliente</td><td>{{ $movimiento->cliente?->nombre }}</td></tr>
<tr><td>Documento</td><td>{{ $movimiento->cliente?->documento }}</td></tr>
<tr><td>Concepto</td><td>{{ $movimiento->concepto }}</td></tr>
<tr><td>Metodo</td><td>{{ $movimiento->metodo_pago }}</td></tr>
<tr><td>Monto</td><td>{{ number_format($movimiento->monto, 2) }}</td></tr>
<tr><td>Referencia</td><td>{{ $movimiento->referencia }}</td></tr>
</table>
</body></html>
