@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1 class="title">Reporte comercial</h1><div class="breadcrumb">Equipo comercial &gt; Reporte comercial</div></div><div class="actions">@can('exportar reporte comercial')<a class="btn secondary" href="{{ route('reportes.comercial.excel', request()->query()) }}">Exportar Excel</a><a class="btn secondary" href="{{ route('reportes.comercial.pdf', request()->query()) }}">Exportar PDF</a>@endcan</div></div>
<form class="card filter-form" method="GET">
    <div class="field"><label>Desde</label><input type="date" name="desde" value="{{ request('desde') }}"></div>
    <div class="field"><label>Hasta</label><input type="date" name="hasta" value="{{ request('hasta') }}"></div>
    <div class="field"><label>Urbanización</label><select name="urbanizacion_id"><option value="">Todas permitidas</option>@foreach($urbanizaciones as $item)<option value="{{ $item->id }}" @selected(request('urbanizacion_id') == $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>Grupo comercial</label><select name="grupo_comercial_id"><option value="">Todos</option>@foreach($grupos as $item)<option value="{{ $item->id }}" @selected(request('grupo_comercial_id') == $item->id)>{{ $item->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>Supervisor comercial</label><select name="supervisor_comercial_id"><option value="">Todos</option>@foreach($supervisoresComerciales as $item)<option value="{{ $item->id }}" @selected(request('supervisor_comercial_id') == $item->id)>{{ $item->name }}</option>@endforeach</select></div>
    <div class="field"><label>Supervisor de ventas</label><select name="supervisor_ventas_id"><option value="">Todos</option>@foreach($supervisoresVentas as $item)<option value="{{ $item->id }}" @selected(request('supervisor_ventas_id') == $item->id)>{{ $item->name }}</option>@endforeach</select></div>
    <div class="field"><label>Vendedor</label><select name="vendedor_id"><option value="">Todos</option>@foreach($vendedores as $item)<option value="{{ $item->id }}" @selected(request('vendedor_id') == $item->id)>{{ $item->name }}</option>@endforeach</select></div>
    <div class="field"><label>Tipo venta</label><select name="tipo_venta"><option value="">Todos</option><option value="contado" @selected(request('tipo_venta')==='contado')>Contado</option><option value="credito" @selected(request('tipo_venta')==='credito')>Crédito</option></select></div>
    <div class="field"><label>Estado venta</label><select name="estado_venta"><option value="">Todos</option>@foreach(['activa','completada','anulada'] as $estado)<option @selected(request('estado_venta')===$estado)>{{ $estado }}</option>@endforeach</select></div>
    <div class="field"><label>Estado reserva</label><select name="estado_reserva"><option value="">Todos</option>@foreach(['activa','vencida','cancelada','convertida'] as $estado)<option @selected(request('estado_reserva')===$estado)>{{ $estado }}</option>@endforeach</select></div>
    <div class="filter-actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('reportes.comercial') }}">Limpiar</a></div>
</form>
<div class="grid stats">
@foreach(['Vendidos'=>$metricas['vendidos'],'Reservas activas'=>$metricas['reservas_activas'],'Ventas contado'=>$metricas['contado'],'Ventas crédito'=>$metricas['credito'],'Monto vendido'=>number_format($metricas['monto'],2),'Conversión %'=>$metricas['conversion'].'%'] as $label=>$value)<div class="card metric"><span class="muted">{{ $label }}</span><div class="stat-value">{{ $value }}</div></div>@endforeach
</div>
<div class="grid dashboard-grid">
    <div class="card"><h2>Ranking de vendedores</h2>@foreach($porVendedor as $nombre=>$total)<p><strong>{{ $nombre }}</strong>: {{ $total }} ventas</p>@endforeach</div>
    <div class="card"><h2>Ventas por grupo</h2>@foreach($porGrupo as $nombre=>$total)<p><strong>{{ $nombre }}</strong>: {{ $total }} ventas</p>@endforeach</div>
</div>
<h2>Ventas filtradas</h2><div class="table-scroll"><table class="table"><thead><tr><th>Fecha</th><th>Urbanización</th><th>Grupo</th><th>Vendedor</th><th>Cliente</th><th>Lote</th><th>Tipo</th><th>Monto</th><th>Estado</th></tr></thead><tbody>@forelse($ventas as $venta)<tr><td>{{ $venta->fecha_venta?->format('d/m/Y') }}</td><td>{{ $venta->urbanizacion?->nombre }}</td><td>{{ $venta->grupoComercial?->nombre ?? '-' }}</td><td>{{ $venta->vendedor?->name ?? $venta->creador?->name ?? '-' }}</td><td>{{ $venta->cliente->nombre }}</td><td>{{ $venta->lote->manzano->codigo }}-{{ $venta->lote->codigo }}</td><td>{{ $venta->tipo_venta }}</td><td>{{ number_format($venta->monto_total,2) }}</td><td><span class="badge {{ $venta->estado }}">{{ $venta->estado }}</span></td></tr>@empty<tr><td colspan="9">No existen ventas para los filtros seleccionados.</td></tr>@endforelse</tbody></table></div>
@endsection
