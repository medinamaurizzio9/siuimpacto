<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#111}.header{border-bottom:3px solid {{ $settings['primary_color'] ?? '#111' }};padding-bottom:12px;margin-bottom:18px;display:table;width:100%}.logo{display:table-cell;width:90px;vertical-align:top}.logo img{max-width:80px;max-height:80px}.company{display:table-cell;vertical-align:top}.title{font-size:18px;font-weight:bold}.muted{color:#555}.receipt-no{font-size:16px;font-weight:bold;text-align:right}.table{width:100%;border-collapse:collapse;margin-top:12px}.table td{border:1px solid #ccc;padding:8px}.amount{font-size:18px;font-weight:bold}.footer{border-top:1px solid #ddd;margin-top:24px;padding-top:10px;color:#555;text-align:center}
    </style>
</head>
<body>
@php($logo = $settings['logo_pdf'] ?: ($settings['logo_main'] ?? null))
<div class="header">
    <div class="logo">@if($logo)<img src="{{ public_path('storage/'.$logo) }}" alt="Logo">@endif</div>
    <div class="company">
        <div class="title">{{ $settings['company_name'] ?? 'IMPACTO URBANIZACIONES' }}</div>
        <div>{{ $settings['system_subtitle'] ?? 'Sistema Integral de Terrenos' }}</div>
        <div class="muted">{{ $settings['direccion'] ?? '' }} {{ $settings['ciudad'] ? '- '.$settings['ciudad'] : '' }}</div>
        <div class="muted">Tel: {{ $settings['telefono'] ?? '' }} {{ $settings['email'] ? '| '.$settings['email'] : '' }} {{ $settings['website'] ? '| '.$settings['website'] : '' }}</div>
    </div>
    <div class="receipt-no">Recibo Nro. {{ str_pad((string) $movimiento->id, 8, '0', STR_PAD_LEFT) }}</div>
</div>
<table class="table">
    <tr><td>Fecha</td><td>{{ $movimiento->fecha->format('d/m/Y') }}</td></tr>
    <tr><td>Cliente</td><td>{{ $movimiento->cliente?->nombre }}</td></tr>
    <tr><td>Documento</td><td>{{ $movimiento->cliente?->documento }}</td></tr>
    <tr><td>Concepto</td><td>{{ ucfirst($movimiento->concepto) }}</td></tr>
    <tr><td>Metodo</td><td>{{ $movimiento->metodo_pago }}</td></tr>
    <tr><td>Monto</td><td class="amount">{{ number_format($movimiento->monto, 2) }}</td></tr>
    <tr><td>Referencia</td><td>{{ $movimiento->referencia }}</td></tr>
</table>
<div class="footer">{{ $settings['footer_text'] ?? 'Version piloto - MVP funcional.' }}</div>
</body>
</html>
