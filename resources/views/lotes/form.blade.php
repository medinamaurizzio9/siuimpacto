@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">{{ $lote->exists ? 'Editar lote' : 'Nuevo lote' }}</h1><a class="btn secondary" href="{{ route('lotes.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="form card" method="POST" action="{{ $lote->exists ? route('lotes.update', $lote) : route('lotes.store') }}">
@csrf @if($lote->exists) @method('PUT') @endif
<div class="field"><label>Manzano</label><select name="manzano_id">@foreach($manzanos as $manzano)<option value="{{ $manzano->id }}" @selected(old('manzano_id', $lote->manzano_id) == $manzano->id)>{{ $manzano->urbanizacion->nombre }} / {{ $manzano->codigo }}</option>@endforeach</select></div>
<div class="field"><label>Codigo</label><input name="codigo" value="{{ old('codigo', $lote->codigo) }}" required></div>
<div class="field"><label>Superficie</label><input name="superficie" type="number" step="0.01" value="{{ old('superficie', $lote->superficie ?? 0) }}"></div>
<div class="field"><label>Precio</label><input name="precio" type="number" step="0.01" value="{{ old('precio', $lote->precio ?? 0) }}"></div>
<div class="field"><label>Tipo de cuota inicial</label><select name="cuota_inicial_tipo">@foreach(\App\Models\Lote::CUOTA_INICIAL_TIPOS as $tipo)<option value="{{ $tipo }}" @selected(old('cuota_inicial_tipo', $lote->cuota_inicial_tipo ?? 'monto') === $tipo)>{{ ucfirst($tipo) }}</option>@endforeach</select></div>
<div class="field"><label>Valor cuota inicial</label><input name="cuota_inicial_valor" type="number" step="0.01" min="0" value="{{ old('cuota_inicial_valor', $lote->cuota_inicial_valor ?? 0) }}"></div>
<div class="field"><label>Fila mapa</label><input name="fila" type="number" value="{{ old('fila', $lote->fila ?? 1) }}"></div>
<div class="field"><label>Columna mapa</label><input name="columna" type="number" value="{{ old('columna', $lote->columna ?? 1) }}"></div>
<div class="field"><label>Coordenada X (%)</label><input name="coord_x" type="number" step="0.01" min="0" max="100" value="{{ old('coord_x', $lote->coord_x ?? 50) }}"></div>
<div class="field"><label>Coordenada Y (%)</label><input name="coord_y" type="number" step="0.01" min="0" max="100" value="{{ old('coord_y', $lote->coord_y ?? 50) }}"></div>
<div class="field full muted">Las coordenadas del mapa deben estar entre 0 y 100 para permanecer dentro del plano.</div>
<div class="field"><label>Estado</label><select name="estado">@foreach($estados as $estado)<option @selected(old('estado', $lote->estado ?? 'disponible') === $estado)>{{ $estado }}</option>@endforeach</select></div>
<div class="field"><label>Motivo cambio de estado</label><input name="motivo_cambio_estado" placeholder="Obligatorio si cambias el estado"></div>
<div class="field full"><label>Observaciones</label><textarea name="observaciones">{{ old('observaciones', $lote->observaciones) }}</textarea></div>
<div class="field full"><button class="btn">Guardar</button></div>
</form>
@endsection
