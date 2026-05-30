@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $reserva->exists ? 'Editar reserva' : 'Nueva reserva' }}</h1><a class="btn secondary" href="{{ route('reservas.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ $reserva->exists ? route('reservas.update', $reserva) : route('reservas.store') }}">
@csrf @if($reserva->exists) @method('PUT') @endif
@include('clientes.partials.buscador', ['selectedCliente' => $clientes->firstWhere('id', (int) old('cliente_id', $reserva->cliente_id)), 'loteSource' => 'lote_id'])
<div class="field"><label>Lote disponible</label><select name="lote_id" id="lote_id">@foreach($lotes as $lote)<option value="{{ $lote->id }}" @selected(old('lote_id', $reserva->lote_id) == $lote->id)>{{ $lote->manzano->urbanizacion->nombre }} / {{ $lote->manzano->codigo }}-{{ $lote->codigo }}</option>@endforeach</select></div>
<div class="field"><label>Fecha reserva</label><input name="fecha_reserva" type="date" value="{{ old('fecha_reserva', optional($reserva->fecha_reserva)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
@if(auth()->user()->hasRole('vendedor'))
    <div class="field"><label>Fecha vencimiento</label><input type="date" value="{{ old('fecha_vencimiento', optional($reserva->fecha_vencimiento)->format('Y-m-d')) }}" disabled><span class="muted">Se calcula automaticamente en dias habiles para asesores.</span></div>
@else
    <div class="field"><label>Fecha vencimiento</label><input name="fecha_vencimiento" type="date" value="{{ old('fecha_vencimiento', optional($reserva->fecha_vencimiento)->format('Y-m-d') ?? now()->addDays(7)->format('Y-m-d')) }}"></div>
@endif
<div class="field"><label>Monto reserva</label><input name="monto_reserva" type="number" step="0.01" value="{{ old('monto_reserva', $reserva->monto_reserva ?? 0) }}"></div>
<div class="field"><label>Tipo de operacion</label><select name="tipo_operacion" required>@foreach($tiposOperacion as $tipo)<option value="{{ $tipo }}" @selected(old('tipo_operacion', $reserva->tipo_operacion ?? 'contado') === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
<div class="field"><label>Metodo de pago</label><select name="metodo_pago">@foreach(['efectivo','transferencia','QR','banco','otro'] as $metodo)<option>{{ $metodo }}</option>@endforeach</select></div>
<div class="field"><label>Referencia</label><input name="referencia" value="{{ old('referencia') }}"></div>
@if($reserva->exists && auth()->user()->can('editar reservas'))
    <div class="field"><label>Estado</label><select name="estado">@foreach(['activa','vencida','cancelada'] as $estado)<option @selected(old('estado', $reserva->estado ?? 'activa') === $estado)>{{ $estado }}</option>@endforeach</select></div>
@endif
<div class="field full"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones', $reserva->observaciones) }}</textarea></div>
<div class="field full"><button class="btn" type="submit">Guardar</button></div>
</form>
@endsection
