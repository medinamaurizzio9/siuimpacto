@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Importar lotes CSV</h1><a class="btn secondary" href="{{ route('lotes.index') }}">Volver</a></div>
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif
<form class="card form" method="POST" enctype="multipart/form-data" action="{{ route('lotes.import.preview') }}">
@csrf
<div class="field full"><label>Archivo CSV</label><input type="file" name="csv" accept=".csv,.txt" required></div>
<div class="field full muted">Columnas: urbanizacion, manzano, lote, superficie_m2, precio_m2, precio_total, estado, coord_x, coord_y, observaciones</div>
<div class="field full"><button class="btn">Validar archivo</button></div>
</form>
@endsection
