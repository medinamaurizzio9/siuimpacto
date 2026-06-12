@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Configuracion comercial</h1></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ route('admin.configuracion.update') }}">
    @csrf @method('PUT')
    <div class="field">
        <label>Dias habiles de reserva para asesores</label>
        <input type="number" min="1" max="30" name="reserva_dias_habiles_asesor" value="{{ old('reserva_dias_habiles_asesor', $reservaDiasHabilesAsesor) }}" required>
        <span class="muted">No cuenta sabados ni domingos.</span>
    </div>
    <div class="field">
        <label>Tipo de cambio USD a Bs</label>
        <input type="number" min="0" step="0.01" name="tipo_cambio_usd_bs" value="{{ old('tipo_cambio_usd_bs', $priceSettings['tipo_cambio_usd_bs']) }}" required>
    </div>
    <div class="field">
        <label>Tipo incremento credito</label>
        <select name="incremento_credito_tipo" required>
            @foreach(['monto' => 'Monto fijo', 'porcentaje' => 'Porcentaje'] as $value => $label)
                <option value="{{ $value }}" @selected(old('incremento_credito_tipo', $priceSettings['incremento_credito_tipo']) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Valor incremento credito</label>
        <input type="number" min="0" step="0.01" name="incremento_credito_valor" value="{{ old('incremento_credito_valor', $priceSettings['incremento_credito_valor']) }}" required>
    </div>
    <div class="field full"><button class="btn">Guardar configuracion</button></div>
</form>
@endsection
