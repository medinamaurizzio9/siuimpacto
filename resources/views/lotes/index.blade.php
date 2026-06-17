@extends('layouts.app')
@section('content')
@inject('pricingService', 'App\Services\LotPricingService')
@php($canSeePrecioOportunidad = auth()->user()?->hasAnyRole(['administrador', 'super administrador']))
@php($isAdminLotes = $canSeePrecioOportunidad)
<div class="topbar">
    <div>
        <p class="context-title">Urbanización: {{ $urbanizacion->nombre }} / Lotes</p>
        <h1 class="title">Lotes</h1>
    </div>
    <div class="actions">
        @can('exportar reportes')<a class="btn secondary" href="{{ route('export.csv', 'lotes') }}">Exportar CSV</a>@endcan
        @if($isAdminLotes)<button class="btn secondary" type="button" id="open-bulk-lotes">Actualizacion Masiva</button>@endif
        @can('crear lotes')<a class="btn secondary" href="{{ route('lotes.import.create') }}">Importar</a><a class="btn" href="{{ route('lotes.create') }}">Nuevo</a>@endcan
    </div>
</div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
@if ($errors->any()) <div class="errors">{{ $errors->first() }}</div> @endif

<form class="filter-form card" method="GET" action="{{ route('lotes.index') }}">
    <div class="field"><label>Buscar lote/código</label><input name="buscar" value="{{ request('buscar') }}" placeholder="Ej. L-15"></div>
    <div class="field"><label>Manzano</label><select name="manzano_id"><option value="">Todos</option>@foreach($manzanos as $manzano)<option value="{{ $manzano->id }}" @selected((string) request('manzano_id') === (string) $manzano->id)>{{ $manzano->codigo }} {{ $manzano->nombre }}</option>@endforeach</select></div>
    <div class="field"><label>Estado</label><select name="estado"><option value="">Todos</option>@foreach($estados as $estado)<option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ ucfirst($estado) }}</option>@endforeach</select></div>
    <div class="field"><label>Superficie desde</label><input type="number" step="0.01" min="0" name="superficie_desde" value="{{ request('superficie_desde') }}"></div>
    <div class="field"><label>Superficie hasta</label><input type="number" step="0.01" min="0" name="superficie_hasta" value="{{ request('superficie_hasta') }}"></div>
    <div class="field"><label>{{ $canSeePrecioOportunidad ? 'Precio oportunidad desde' : 'Precio real desde' }}</label><input type="number" step="0.01" min="0" name="precio_desde" value="{{ request('precio_desde') }}"></div>
    <div class="field"><label>{{ $canSeePrecioOportunidad ? 'Precio oportunidad hasta' : 'Precio real hasta' }}</label><input type="number" step="0.01" min="0" name="precio_hasta" value="{{ request('precio_hasta') }}"></div>
    <div class="filter-actions"><button class="btn" type="submit">Filtrar</button><a class="btn secondary" href="{{ route('lotes.index') }}">Limpiar</a></div>
</form>

@if($isAdminLotes)
<div class="card bulk-lotes-panel" id="actualizacion-masiva-lotes" hidden>
    <h2>Actualizacion Masiva</h2>
    <p class="muted">Cantidad de lotes seleccionados: <strong id="selected-lotes-count">0</strong></p>
    <div class="errors" id="bulk-lotes-message" hidden>Seleccione al menos un lote.</div>
    <form class="form compact" method="POST" action="{{ route('lotes.comercial-masivo') }}">
        @csrf
        <div id="selected-lotes-inputs"></div>
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
        <thead><tr>@if($isAdminLotes)<th><label class="bulk-check"><input type="checkbox" id="select-all-lotes"> <span>Seleccionar todos</span></label></th>@endif<th><x-sort-link field="codigo">Lote</x-sort-link></th><th><x-sort-link field="manzano">Manzano</x-sort-link></th><th><x-sort-link field="superficie">Superficie</x-sort-link></th>@if($canSeePrecioOportunidad)<th><x-sort-link field="precio">Precio Oportunidad</x-sort-link></th>@endif<th><x-sort-link field="precio_real">Precio Real</x-sort-link></th><th><x-sort-link field="cuota_inicial">Cuota inicial</x-sort-link></th><th><x-sort-link field="estado">Estado</x-sort-link></th><th></th></tr></thead>
        <tbody>
        @forelse ($lotes as $lote)
            @php($pricePayload = $pricingService->payload($lote))
            @php($quickFormId = 'lote-comercial-'.$lote->id)
            <tr>
                @if($isAdminLotes)<td><input class="lote-bulk-checkbox" type="checkbox" value="{{ $lote->id }}" aria-label="Seleccionar lote {{ $lote->codigo }}"></td>@endif
                <td>{{ $lote->codigo }}</td>
                <td>{{ $lote->manzano->codigo }}</td>
                <td>{{ number_format($lote->superficie, 2) }} m2</td>
                @if($canSeePrecioOportunidad)
                    <td>
                        @if($isAdminLotes)
                            <input class="inline-money-input" form="{{ $quickFormId }}" name="precio_oportunidad_usd" type="number" step="0.01" min="0" value="{{ number_format((float) $lote->precio, 2, '.', '') }}" aria-label="Precio Oportunidad">
                        @else
                            {{ $pricingService->formatUsd((float) $lote->precio) }}
                        @endif
                    </td>
                @endif
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
            <tr><td colspan="{{ ($isAdminLotes ? 1 : 0) + ($canSeePrecioOportunidad ? 8 : 7) }}">No se encontraron lotes con los filtros seleccionados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if ($lotes->hasPages())
    <div class="pagination-wrapper">
        {{ $lotes->links('pagination::bootstrap-5') }}
    </div>
@endif

@if($isAdminLotes)
<script>
document.addEventListener('DOMContentLoaded', () => {
    const panel = document.getElementById('actualizacion-masiva-lotes');
    const openButton = document.getElementById('open-bulk-lotes');
    const selectAll = document.getElementById('select-all-lotes');
    const checkboxes = Array.from(document.querySelectorAll('.lote-bulk-checkbox'));
    const count = document.getElementById('selected-lotes-count');
    const inputs = document.getElementById('selected-lotes-inputs');
    const message = document.getElementById('bulk-lotes-message');

    function selectedIds() {
        return checkboxes.filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);
    }

    function syncSelection() {
        const ids = selectedIds();
        count.textContent = ids.length;
        inputs.innerHTML = ids.map((id) => `<input type="hidden" name="lote_ids[]" value="${id}">`).join('');
        message.hidden = ids.length > 0;
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && ids.length === checkboxes.length;
            selectAll.indeterminate = ids.length > 0 && ids.length < checkboxes.length;
        }
    }

    openButton?.addEventListener('click', () => {
        panel.hidden = false;
        syncSelection();
        if (selectedIds().length === 0) {
            message.hidden = false;
        }
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    selectAll?.addEventListener('change', () => {
        checkboxes.forEach((checkbox) => checkbox.checked = selectAll.checked);
        syncSelection();
    });

    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', syncSelection));

    panel?.querySelector('form')?.addEventListener('submit', (event) => {
        syncSelection();
        if (selectedIds().length === 0) {
            event.preventDefault();
            message.hidden = false;
            return;
        }

        if (!confirm('Confirma aplicar la actualizacion masiva?')) {
            event.preventDefault();
        }
    });

    syncSelection();
});
</script>
@endif
@endsection
