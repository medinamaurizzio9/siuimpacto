@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $venta->exists ? 'Editar venta' : 'Nueva venta' }}</h1><a class="btn secondary" href="{{ route('ventas.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ $venta->exists ? route('ventas.update', $venta) : route('ventas.store') }}">
@csrf @if($venta->exists) @method('PUT') @endif
@include('clientes.partials.buscador', ['selectedCliente' => $clientes->firstWhere('id', (int) old('cliente_id', $venta->cliente_id)), 'loteSource' => 'lote_id'])
<div class="field"><label>Lote disponible</label><select name="lote_id" id="lote_id">@foreach($lotes as $lote)<option value="{{ $lote->id }}" @selected(old('lote_id', $venta->lote_id) == $lote->id)>{{ $lote->manzano->urbanizacion->nombre }} / {{ $lote->manzano->codigo }}-{{ $lote->codigo }} / {{ number_format($lote->precio, 2) }}</option>@endforeach</select></div>
<div class="field"><label>Fecha venta</label><input name="fecha_venta" type="date" value="{{ old('fecha_venta', optional($venta->fecha_venta)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
<div class="field"><label>Precio final</label><input name="precio_final" type="number" step="0.01" value="{{ old('precio_final', $venta->precio_final ?? 0) }}"></div>
<div class="field"><label>Cuota inicial</label><input name="cuota_inicial" type="number" step="0.01" value="{{ old('cuota_inicial', $venta->cuota_inicial ?? 0) }}"></div>
<div class="field"><label>Numero cuotas</label><input name="numero_cuotas" type="number" value="{{ old('numero_cuotas', $venta->numero_cuotas ?? 12) }}"></div>
<div class="field"><label>Metodo de pago</label><select name="metodo_pago">@foreach(['efectivo','transferencia','QR','banco','otro'] as $metodo)<option @selected(old('metodo_pago', 'efectivo') === $metodo)>{{ $metodo }}</option>@endforeach</select></div>
<div class="field"><label>Referencia</label><input name="referencia" value="{{ old('referencia') }}"></div>
<div class="field"><label>Estado</label><select name="estado">@foreach(['activa','completada','anulada'] as $estado)<option @selected(old('estado', $venta->estado ?? 'activa') === $estado)>{{ $estado }}</option>@endforeach</select></div>
<label style="display:flex;gap:8px;align-items:center;margin-top:30px;"><input type="checkbox" name="admin_confirma_reserva" value="1" style="width:auto;"> Confirmacion administrativa para lote reservado a otro cliente</label>
<div class="field full"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones', $venta->observaciones) }}</textarea></div>
<div class="field full"><button class="btn">Guardar</button></div>
</form>
@endsection
