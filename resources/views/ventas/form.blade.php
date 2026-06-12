@extends('layouts.app')
@section('content')
@inject('pricingService', 'App\Services\LotPricingService')
<div class="topbar"><h1 class="title">{{ $venta->exists ? 'Editar venta' : 'Nueva venta' }}</h1><a class="btn secondary" href="{{ route('ventas.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ $venta->exists ? route('ventas.update', $venta) : route('ventas.store') }}">
@csrf @if($venta->exists) @method('PUT') @endif
@include('clientes.partials.buscador', ['selectedCliente' => $clientes->firstWhere('id', (int) old('cliente_id', $venta->cliente_id)), 'loteSource' => 'lote_id'])
<div class="field"><label>Lote disponible</label><select name="lote_id" id="lote_id">@foreach($lotes as $lote)@php($pricePayload = $pricingService->payload($lote))<option value="{{ $lote->id }}" data-base-usd="{{ $pricePayload['base_usd'] }}" data-base-bs="{{ $pricePayload['base_bs'] }}" data-credit-usd="{{ $pricePayload['credit_usd'] }}" data-credit-bs="{{ $pricePayload['credit_bs'] }}" data-initial-base-usd="{{ $pricePayload['initial_base_usd'] }}" data-initial-base-bs="{{ $pricePayload['initial_base_bs'] }}" data-initial-credit-usd="{{ $pricePayload['initial_credit_usd'] }}" data-initial-credit-bs="{{ $pricePayload['initial_credit_bs'] }}" @selected(old('lote_id', $venta->lote_id) == $lote->id)>{{ $lote->manzano->urbanizacion->nombre }} / {{ $lote->manzano->codigo }}-{{ $lote->codigo }} / Precio Oportunidad: {{ $pricingService->formatUsd($pricePayload['base_usd']) }} / Precio Real: {{ $pricingService->formatUsd($pricePayload['credit_usd']) }} / Cuota inicial config.: {{ $lote->cuotaInicialTexto() }}</option>@endforeach</select><small class="muted" id="lote-cuota-inicial-info"></small></div>
<div class="field"><label>Tipo de operacion</label><select name="tipo_operacion" id="tipo_operacion" required>@foreach(\App\Models\Reserva::TIPOS_OPERACION as $tipo)<option value="{{ $tipo }}" @selected(old('tipo_operacion', $venta->tipo_operacion ?? ((int) ($venta->numero_cuotas ?? 12) > 0 ? 'credito' : 'contado')) === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
<div class="field"><label>Fecha venta</label><input name="fecha_venta" type="date" value="{{ old('fecha_venta', optional($venta->fecha_venta)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
<div class="field"><label>Precio final</label><input name="precio_final" type="number" step="0.01" value="{{ old('precio_final', $venta->precio_final ?? 0) }}"></div>
<div class="field"><label>Cuota inicial</label><input name="cuota_inicial" type="number" step="0.01" value="{{ old('cuota_inicial', $venta->cuota_inicial ?? 0) }}"></div>
<div class="field"><label>Numero cuotas</label><input name="numero_cuotas" type="number" value="{{ old('numero_cuotas', $venta->numero_cuotas ?? 12) }}"></div>
<div class="field"><label>Metodo de pago</label><select name="metodo_pago">@foreach(['efectivo','transferencia','QR','banco','otro'] as $metodo)<option @selected(old('metodo_pago', $initialMovement?->metodo_pago ?? 'efectivo') === $metodo)>{{ $metodo }}</option>@endforeach</select></div>
<div class="field"><label>Referencia</label><input name="referencia" value="{{ old('referencia', $initialMovement?->referencia) }}"></div>
<div class="field"><label>Estado</label><select name="estado">@foreach($venta->estado === 'anulada' ? ['anulada'] : ['activa','completada'] as $estado)<option @selected(old('estado', $venta->estado ?? 'activa') === $estado)>{{ $estado }}</option>@endforeach</select></div>
<label style="display:flex;gap:8px;align-items:center;margin-top:30px;"><input type="checkbox" name="admin_confirma_reserva" value="1" style="width:auto;"> Confirmacion administrativa para lote reservado a otro cliente</label>
<div class="field full"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones', $venta->observaciones) }}</textarea></div>
@if($venta->exists)
<div class="field full"><label>Motivo del cambio</label><textarea name="motivo_cambio" required placeholder="Explique el motivo del cambio de esta venta">{{ old('motivo_cambio') }}</textarea></div>
@endif
<div class="field full"><button class="btn">Guardar</button></div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const loteSelect = document.getElementById('lote_id');
    const tipoSelect = document.getElementById('tipo_operacion');
    const cuotaInfo = document.getElementById('lote-cuota-inicial-info');
    const precioFinal = document.querySelector('[name="precio_final"]');
    const cuotaInicial = document.querySelector('[name="cuota_inicial"]');
    const moneyUsd = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const moneyBs = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (! loteSelect || ! cuotaInfo) {
        return;
    }

    const updateCuotaInicialInfo = function () {
        const selected = loteSelect.options[loteSelect.selectedIndex];
        const isCredit = tipoSelect?.value === 'credito';
        const priceUsd = Number(isCredit ? selected?.dataset.creditUsd : selected?.dataset.baseUsd) || 0;
        const priceBs = Number(isCredit ? selected?.dataset.creditBs : selected?.dataset.baseBs) || 0;
        const initialUsd = Number(isCredit ? selected?.dataset.initialCreditUsd : selected?.dataset.initialBaseUsd) || 0;
        const initialBs = Number(isCredit ? selected?.dataset.initialCreditBs : selected?.dataset.initialBaseBs) || 0;

        if (!{{ $venta->exists ? 'true' : 'false' }}) {
            if (precioFinal) precioFinal.value = priceUsd.toFixed(2);
            if (cuotaInicial) cuotaInicial.value = initialUsd.toFixed(2);
        }

        cuotaInfo.textContent = `Precio operacion: $us ${moneyUsd.format(priceUsd)} | Precio operacion Bs: Bs ${moneyBs.format(priceBs)} | Cuota inicial configurada del lote: $us ${moneyUsd.format(initialUsd)} | Cuota inicial Bs: Bs ${moneyBs.format(initialBs)}`;
    };

    loteSelect.addEventListener('change', updateCuotaInicialInfo);
    tipoSelect?.addEventListener('change', updateCuotaInicialInfo);
    updateCuotaInicialInfo();
});
</script>
@endsection
