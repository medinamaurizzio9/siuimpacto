@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Exportaciones</h1></div>
<div class="grid report-card-grid">
    @foreach(['lotes' => 'Lotes', 'clientes' => 'Clientes', 'ventas' => 'Ventas', 'reservas' => 'Reservas', 'cuotas' => 'Cuotas', 'caja' => 'Caja'] as $tipo => $label)
        <a class="card report-link-card" href="{{ route('export.csv', $tipo) }}"><strong>{{ $label }}</strong><span>Descargar CSV de {{ strtolower($label) }}.</span></a>
    @endforeach
    <a class="card report-link-card" href="{{ route('reportes.csv', 'ingresos') }}"><strong>Ingresos</strong><span>Descargar CSV solo de ingresos.</span></a>
</div>
@endsection
