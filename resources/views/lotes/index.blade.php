@extends('layouts.app')
@section('content')
@inject('pricingService', 'App\Services\LotPricingService')
@php($isAdminLotes = auth()->user()?->hasRole('administrador'))
<div class="topbar">
    <div>
        <p class="context-title">Urbanización: {{ $urbanizacion->nombre }} / Lotes</p>
        <h1 class="title">Lotes</h1>
    </div>
    <div class="actions">
        @can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'lotes') }}">Exportar CSV</a>@endcan
        @if($isAdminLotes)<a class="btn secondary" href="#actualizacion-masiva-lotes">Actualizacion Masiva</a>@endif
        @can('crear lotes')<a class="btn secondary" href="{{ route('lotes.import.create') }}">Importar</a><a class="btn" href="{{ route('lotes.create') }}">Nuevo</a>@endcan
    </div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif

<form class="filter-form card" method="GET" action="{{ route('lotes.index') }}">
    <div class="field"><label>Buscar lote/código</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Ej. L-15"></div>
    <div class="field"><label>Manzano</label><select name="manzano_id"><option value="">Todos</option>@foreach($manzanos as $manzano)<option value="{{ $manzano->id }}" @selected((string) request('manzano_id') === (string) $manzano->id)>{{ $manzano->codigo }} {{ $manzano->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach($estados as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
    <div class="field"><label>Superficie desde</label><input type="number" step="0.01" min="0" name="superficie_desde" value="{{ request('superficie_desde') }}"></div>
    <div class="field"><label>Superficie hasta</label><input type="number" step="0.01" min="0" name="superficie_hasta" value="{{ request('superficie_hasta') }}"></div>
    <div class="field"><label>Precio oportunidad desde</label><input type="number" step="0.01" min="0" name="precio_desde" value="{{ request('precio_desde') }}"></div>
    <div class="field"><label>Precio oportunidad hasta</label><input type="number" step="0.01" min="0" name="precio_hasta" value="{{ request('precio_hasta') }}"></div>
    <div class="filter-actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('lotes.index') }}">Limpiar</a></div>
</form>

@if($isAdminLotes)
<div class="card" id="actualizacion-masiva-lotes" style="margin-bottom:18px;">
    <h2>Actualizacion Masiva</h2>
    <p class="muted">Antes de aplicar: todos los lotes de la urbanizacion actual: {{ $bulkCounts['todos'] }}. Lotes filtrados actualmente: {{ $bulkCounts['filtrados'] }}.</p>
    <form class="form compact" method="POST" action="{{ route('lotes.comercial-masivo') }}" onsubmit="return confirm('Confirma aplicar la actualizacion masiva?');">
        @csrf
        @foreach(['buscar', 'estado', 'superficie_desde', 'superficie_hasta', 'precio_desde', 'precio_hasta'] as $filter)
            <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
        @endforeach
        <div class="field">
            <label>Alcance</label>
            <select name="scope">
                <option value="todos">Todos los lotes de la urbanizacion actual ({{ $bulkCounts['todos'] }})</option>
                <option value="filtrados">Solo lotes filtrados actualmente ({{ $bulkCounts['filtrados'] }})</option>
                <option value="manzano">Solo manzano seleccionado</option>
            </select>
        </div>
        <div class="field">
            <label>Manzano</label>
            <select name="manzano_id">
                <option value="">Selecciona si el alcance es manzano</option>
                @foreach($manzanos as $manzano)
                    <option value="{{ $manzano->id }}" @selected((string) request('manzano_id') === (string) $manzano->id)>{{ $manzano->codigo }} {{ $manzano->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label>Operacion</label>
            <select name="operation">
                <option value="reemplazar_precio_oportunidad">Reemplazar Precio Oportunidad</option>
                <option value="incrementar_precio_oportunidad_monto">Incrementar Precio Oportunidad por monto</option>
                <option value="incrementar_precio_oportunidad_porcentaje">Incrementar Precio Oportunidad por porcentaje</option>
                <option value="reemplazar_cuota">Reemplazar cuota inicial</option>
                <option value="incrementar_cuota_monto">Incrementar cuota inicial por monto</option>
            </select>
        </div>
        <div class="field"><label>Valor</label><input name="valor" type="number" step="0.01" min="0"></div>
        <div class="field full"><button class="btn" type="submit">Aplicar actualizacion masiva</button></div>
    </form>
</div>
@endif

<div class="table-scroll">
    <table class="table">
        <thead><tr><th>Lote</th><th>Manzano</th><th>Superficie</th><th>Precio Oportunidad</th><th>Precio Real</th><th>Cuota inicial</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @forelse ($lotes as $lote)
            @php($pricePayload = $pricingService->payload($lote))
            @php($quickFormId = 'lote-comercial-'.$lote->id)
            <tr>
                <td>{{ $lote->codigo }}</td>
                <td>{{ $lote->manzano->codigo }}</td>
                <td>{{ number_format($lote->superficie, 2) }} m2</td>
                <td>
                    @if($isAdminLotes)
                        <input class="inline-money-input" form="{{ $quickFormId }}" name="precio_oportunidad_usd" type="number" step="0.01" min="0" value="{{ number_format((float) $lote->precio, 2, '.', '') }}" aria-label="Precio Oportunidad">
                    @else
                        {{ $pricingService->formatUsd((float) $lote->precio) }}
                    @endif
                </td>
                <td>{{ $pricingService->formatUsd($pricePayload['credit_usd']) }}</td>
                <td>
                    @if($isAdminLotes)
                        <input class="inline-money-input" form="{{ $quickFormId }}" name="cuota_inicial_valor" type="number" step="0.01" min="0" value="{{ number_format((float) $lote->cuota_inicial_valor, 2, '.', '') }}" aria-label="Cuota Inicial">
                        <span class="mini-badge">{{ $lote->cuotaInicialTexto() }}</span>
                    @else
                        {{ $lote->cuotaInicialTexto() }}
                    @endif
                </td>
                <td><span class="badge {{ $lote->estado }}">{{ $lote->estado }}</span></td>
                <td class="actions">
                    @if($isAdminLotes)
                        <form id="{{ $quickFormId }}" method="POST" action="{{ route('lotes.comercial-rapido', $lote) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn secondary" type="submit">Editar rapido</button>
                        </form>
                    @endif
                    @can('editar lotes')<a class="btn secondary" href="{{ route('lotes.edit', $lote) }}">Editar</a>@endcan @can('eliminar lotes')<form method="POST" action="{{ route('lotes.destroy', $lote) }}" onsubmit="return confirm('Confirma eliminar este lote?');">@csrf @method('DELETE')<button class="btn danger" type="submit">Eliminar</button></form>@endcan</td>
            </tr>
        @empty
            <tr><td colspan="8">No se encontraron lotes con los filtros seleccionados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($lotes->hasPages())
    <div class="pagination-wrapper">
        {{ $lotes->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
