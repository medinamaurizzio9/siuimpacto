@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Validacion de importacion</h1><a class="btn secondary" href="{{ route('lotes.import.create') }}">Volver</a></div>
@if($errors)
<div class="errors"><strong>Corrige estos errores antes de importar:</strong><ul>@foreach($errors as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@else
<div class="status">Archivo valido. {{ count($rows) }} lotes listos para importar.</div>
<form method="POST" action="{{ route('lotes.import.store') }}">@csrf
<input type="hidden" name="rows" value="{{ e(json_encode($rows)) }}">
<button class="btn" onclick="return confirm('Confirma importar estos lotes?')">Importar lotes</button>
</form>
@endif
<table class="table" style="margin-top:18px;"><thead><tr><th>Urbanizacion</th><th>Manzano</th><th>Lote</th><th>Estado</th><th>Precio</th><th>Cuota inicial</th></tr></thead><tbody>
@foreach($rows as $row)<tr><td>{{ $row['urbanizacion'] }}</td><td>{{ $row['manzano'] }}</td><td>{{ $row['lote'] }}</td><td>{{ $row['estado'] }}</td><td>{{ $row['precio_total'] }}</td><td>{{ $row['cuota_inicial_tipo'] }} {{ $row['cuota_inicial_valor'] }}</td></tr>@endforeach
</tbody></table>
@endsection
