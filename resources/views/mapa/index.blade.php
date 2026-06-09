@extends('layouts.app')
@section('content')
@php
    $isVendedorMapa = auth()->user()?->hasRole('vendedor');
    $isSupervisorMapa = auth()->user()?->hasRole('supervisor');
    $isAdminMapa = auth()->user()?->hasRole('administrador');
@endphp
<div class="topbar">
    <h1 class="title">Mapa de disponibilidad</h1>
    <div class="actions">
        @if($urbanizacion?->plano_imagen)
            @role('administrador')
                <button class="btn" id="toggle-edit" type="button">Activar ubicacion manual</button>
            @endrole
        @endif
    </div>
</div>

<form method="GET" action="{{ route('mapa') }}" class="card form" style="margin-bottom:18px;">
    <div class="field">
        <label>Urbanizacion</label>
        <select name="urbanizacion_id" onchange="this.form.submit()">
            @foreach($urbanizaciones as $item)
                <option value="{{ $item->id }}" @selected($urbanizacion?->id === $item->id)>{{ $item->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Manzano</label>
        <select name="manzano_id">
            <option value="">Todos</option>
            @foreach($urbanizacion?->manzanos ?? [] as $manzano)
                <option value="{{ $manzano->id }}" @selected($manzanoId === $manzano->id)>{{ $manzano->codigo }}</option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Estado</label>
        <select name="estado">
            <option value="">Todos</option>
            @foreach(['disponible','reservado','vendido','bloqueado'] as $item)
                <option value="{{ $item }}" @selected($estado === $item)>{{ $item }}</option>
            @endforeach
        </select>
    </div>
    <div class="field"><label>Buscar lote</label><input name="lote" value="{{ $busqueda }}" placeholder="Ej. 01"></div>
    <label class="check-row"><input type="checkbox" name="sin_ubicacion" value="1" @checked($sinUbicacion)> Mostrar solo lotes sin ubicacion</label>
    <div class="field"><label>&nbsp;</label><button class="btn" type="submit">Filtrar</button></div>
</form>

@if($urbanizacion)
    @php
        $allLotes = $urbanizacion->manzanos->flatMap->lotes;
        $lotes = $urbanizacion->manzanos
            ->when($manzanoId, fn ($items) => $items->where('id', $manzanoId))
            ->flatMap->lotes
            ->filter(fn ($lote) => ! $estado || $lote->estado === $estado)
            ->filter(fn ($lote) => ! $busqueda || str_contains(strtolower($lote->codigo), strtolower($busqueda)))
            ->filter(fn ($lote) => ! $sinUbicacion || is_null($lote->coord_x) || is_null($lote->coord_y));
        $locatedLotes = $lotes->filter(fn ($lote) => ! is_null($lote->coord_x) && ! is_null($lote->coord_y));
        $unlocatedLotes = $allLotes->filter(fn ($lote) => is_null($lote->coord_x) || is_null($lote->coord_y))->values();
        $counts = $allLotes->countBy('estado');
    @endphp

    <div class="card">
        <div class="map-toolbar">
            <h2>{{ $urbanizacion->nombre }}</h2>
            <div class="legend">
                @foreach(['disponible' => 'Disponible', 'vendido' => 'Vendido', 'reservado' => 'Reservado', 'bloqueado' => 'Bloqueado'] as $key => $label)
                    <span><i class="legend-dot {{ $key }}"></i>{{ $label }}: {{ $counts[$key] ?? 0 }}</span>
                @endforeach
                <span>Sin ubicacion: {{ $unlocatedLotes->count() }}</span>
            </div>
        </div>

        @if(! $urbanizacion->plano_imagen)
            <div class="empty-plan">Esta urbanizacion aun no tiene plano cargado</div>
        @else
            @role('administrador')
                <div class="edit-panel" id="edit-panel" hidden>
                    <div class="field">
                        <label>Urbanizacion</label>
                        <select onchange="window.location='{{ route('mapa') }}?urbanizacion_id='+this.value">
                            @foreach($urbanizaciones as $item)
                                <option value="{{ $item->id }}" @selected($urbanizacion->id === $item->id)>{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Manzano</label>
                        <select id="edit-manzano">
                            <option value="">Todos</option>
                            @foreach($urbanizacion->manzanos->sortBy('orden') as $manzano)
                                <option value="{{ $manzano->id }}">{{ $manzano->codigo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Lote</label>
                        <select id="selected-lote">
                            <option value="">Selecciona un lote</option>
                            @foreach($urbanizacion->manzanos->sortBy('orden') as $manzano)
                                @foreach($manzano->lotes->sortBy('codigo') as $lote)
                                    @php
                                        $canReservarOption = $lote->estado === 'disponible' && auth()->user()?->can('crear reservas');
                                        $canVenderOption = ! $isVendedorMapa && ! $isSupervisorMapa && auth()->user()?->can('crear ventas') && in_array($lote->estado, ['disponible', 'reservado'], true);
                                        $canEditarOption = ! $isVendedorMapa && ! $isSupervisorMapa && auth()->user()?->can('editar lotes');
                                    @endphp
                                    <option value="{{ $lote->id }}" data-manzano="{{ $manzano->id }}" data-label="{{ $manzano->codigo }}-{{ $lote->codigo }}" data-estado="{{ $lote->estado }}" data-cuota-inicial="{{ $lote->cuotaInicialTexto() }}" data-has-position="{{ ! is_null($lote->coord_x) && ! is_null($lote->coord_y) ? '1' : '0' }}" data-detail-url="{{ route('lotes.show', $lote) }}" data-reserva-url="{{ route('reservas.create', ['lote_id' => $lote->id]) }}" data-venta-url="{{ route('ventas.create', ['lote_id' => $lote->id]) }}" data-edit-url="{{ route('lotes.edit', $lote) }}" data-can-reservar="{{ $canReservarOption ? '1' : '0' }}" data-can-vender="{{ $canVenderOption ? '1' : '0' }}" data-can-editar="{{ $canEditarOption ? '1' : '0' }}">{{ $manzano->codigo }}-{{ $lote->codigo }}{{ is_null($lote->coord_x) || is_null($lote->coord_y) ? ' (sin ubicacion)' : '' }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <button class="btn secondary" id="next-unlocated" type="button">Siguiente lote sin ubicacion</button>
                    <button class="btn secondary" id="toggle-edit-lock" type="button">Bloquear edicion</button>
                    <button class="btn danger" id="clear-position" type="button">Quitar ubicacion del lote seleccionado</button>
                    <div id="mouse-coordinates" class="muted">X: --%, Y: --%</div>
                    <div id="position-message" class="muted">Modo ubicacion activo: selecciona un lote y haz clic sobre su posicion exacta en el plano</div>
                </div>
            @endrole

            <p class="muted map-help">Las posiciones se guardan proporcionalmente, por eso se mantienen en celular y escritorio.</p>

            <div class="map-shell">
                <div class="map-zoom-controls">
                    <button class="btn secondary" type="button" data-zoom-in title="Acercar" aria-label="Acercar">Zoom +</button>
                    <button class="btn secondary" type="button" data-zoom-out title="Alejar" aria-label="Alejar">Zoom -</button>
                    <button class="btn secondary" type="button" data-zoom-reset title="Restablecer vista" aria-label="Restablecer vista">Restablecer</button>
                    <button class="btn secondary" type="button" data-zoom-fullscreen title="Pantalla completa" aria-label="Pantalla completa">Pantalla completa</button>
                    <span class="zoom-value" data-zoom-value>100%</span>
                </div>

                <div class="plan-map-viewport" id="plan-map">
                    <div class="plan-map-layer" id="plan-map-layer">
                        <img class="plan-map-image" id="plan-image" src="{{ asset('storage/'.$urbanizacion->plano_imagen) }}" alt="Plano {{ $urbanizacion->nombre }}">
                        @foreach($locatedLotes as $lote)
                            @php
                                $manzano = $lote->manzano;
                                $canReservar = $lote->estado === 'disponible' && auth()->user()?->can('crear reservas');
                                $canVender = ! $isVendedorMapa && ! $isSupervisorMapa && auth()->user()?->can('crear ventas') && in_array($lote->estado, ['disponible', 'reservado'], true);
                                $canEditar = ! $isVendedorMapa && ! $isSupervisorMapa && auth()->user()?->can('editar lotes');
                            @endphp
                            <button type="button" class="map-point lot-point {{ $lote->estado }}" data-lote-id="{{ $lote->id }}" data-label="{{ $manzano->codigo }}-{{ $lote->codigo }}" data-urbanizacion="{{ $urbanizacion->nombre }}" data-manzano="{{ $manzano->codigo }}" data-lote="{{ $lote->codigo }}" data-superficie="{{ number_format($lote->superficie, 2) }} m2" data-precio="{{ number_format($lote->precio, 2) }}" data-cuota-inicial="{{ $lote->cuotaInicialTexto() }}" data-estado="{{ $lote->estado }}" data-detail-url="{{ route('lotes.show', $lote) }}" data-reserva-url="{{ route('reservas.create', ['lote_id' => $lote->id]) }}" data-venta-url="{{ route('ventas.create', ['lote_id' => $lote->id]) }}" data-edit-url="{{ route('lotes.edit', $lote) }}" data-can-reservar="{{ $canReservar ? '1' : '0' }}" data-can-vender="{{ $canVender ? '1' : '0' }}" data-can-editar="{{ $canEditar ? '1' : '0' }}" title="{{ $manzano->codigo }}-{{ $lote->codigo }}" style="left: {{ max(0, min(100, (float) $lote->coord_x)) }}%; top: {{ max(0, min(100, (float) $lote->coord_y)) }}%;"><span>{{ $lote->codigo }}</span></button>
                        @endforeach
                    </div>
                </div>

                <div id="lotModalOverlay" class="modal-overlay hidden"></div>
                <div id="lote-map-modal" class="lot-modal hidden" role="dialog" aria-modal="true" aria-labelledby="lotModalTitle">
                    <div class="map-dialog">
                        <h2 id="lotModalTitle" data-modal-title>Detalle de lote</h2>
                        <p><strong>Urbanizacion:</strong> <span data-modal-urbanizacion></span></p>
                        <p><strong>Manzano:</strong> <span data-modal-manzano></span></p>
                        <p><strong>Lote:</strong> <span data-modal-lote></span></p>
                        <p><strong>Superficie:</strong> <span data-modal-superficie></span></p>
                        <p><strong>Precio:</strong> <span data-modal-precio></span></p>
                        <p><strong>Cuota inicial:</strong> <span data-modal-cuota-inicial></span></p>
                        <p><strong>Estado:</strong> <span class="badge" data-modal-estado></span></p>
                        <p class="muted" data-modal-message hidden></p>
                        <div class="actions">
                            <a class="btn secondary" href="" data-modal-link="detalle">Ver detalle</a>
                            <a class="btn secondary" href="" data-modal-link="reservar" hidden>Reservar lote</a>
                            <a class="btn secondary" href="" data-modal-link="vender" hidden>Vender lote</a>
                            <a class="btn secondary" href="" data-modal-link="editar" hidden>Editar lote</a>
                            <button type="button" id="lotModalClose" class="btn">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@else
    <div class="card">Crea una urbanizacion para ver el mapa.</div>
@endif

<script src="{{ asset('js/map-zoom.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const map = document.getElementById('plan-map');
    const layer = document.getElementById('plan-map-layer');
    const image = document.getElementById('plan-image');
    if (!map || !layer || !image || typeof window.createImpactoMapZoom !== 'function') return;

    let editMode = false;
    let editLocked = false;
    let dragging = null;
    const panel = document.getElementById('edit-panel');
    const selected = document.getElementById('selected-lote');
    const editManzano = document.getElementById('edit-manzano');
    const message = document.getElementById('position-message');
    const mouseCoordinates = document.getElementById('mouse-coordinates');
    const toggleEdit = document.getElementById('toggle-edit');
    const toggleLock = document.getElementById('toggle-edit-lock');
    const csrf = '{{ csrf_token() }}';
    const zoomController = window.createImpactoMapZoom({
        map,
        layer,
        zoomIn: map.closest('.map-shell')?.querySelector('[data-zoom-in]'),
        zoomOut: map.closest('.map-shell')?.querySelector('[data-zoom-out]'),
        reset: map.closest('.map-shell')?.querySelector('[data-zoom-reset]'),
        fullscreen: map.closest('.map-shell')?.querySelector('[data-zoom-fullscreen]'),
        zoomLabel: map.closest('.map-shell')?.querySelector('[data-zoom-value]'),
        canPan: () => !editMode,
        shouldIgnorePanTarget: (target) => Boolean(target.closest('.map-point')),
    });
    const lotModal = window.createImpactoLotModal?.({
        modal: document.getElementById('lote-map-modal'),
        overlay: document.getElementById('lotModalOverlay'),
        closeButton: document.getElementById('lotModalClose'),
    });

    toggleEdit?.addEventListener('click', () => {
        editMode = !editMode;
        panel.hidden = !editMode;
        map.classList.toggle('editing', editMode && !editLocked);
        toggleEdit.textContent = editMode ? 'Desactivar ubicacion manual' : 'Activar ubicacion manual';
        message.textContent = editMode
            ? 'Modo ubicacion activo: selecciona un lote y haz clic sobre su posicion exacta en el plano'
            : 'Modo ubicacion desactivado.';
        if (!editMode && mouseCoordinates) mouseCoordinates.textContent = 'X: --%, Y: --%';
    });

    toggleLock?.addEventListener('click', () => {
        editLocked = !editLocked;
        toggleLock.textContent = editLocked ? 'Desbloquear edicion' : 'Bloquear edicion';
        map.classList.toggle('editing', editMode && !editLocked);
        message.textContent = editLocked ? 'Edicion bloqueada para evitar movimientos accidentales.' : 'Edicion desbloqueada.';
    });

    editManzano?.addEventListener('change', () => {
        const manzanoId = editManzano.value;
        [...selected.options].forEach((option) => {
            option.hidden = option.value && manzanoId && option.dataset.manzano !== manzanoId;
        });
        selected.value = '';
    });

    document.getElementById('next-unlocated')?.addEventListener('click', () => {
        const next = [...selected.options].find((option) => option.value && option.dataset.hasPosition === '0' && !option.hidden);
        if (next) {
            selected.value = next.value;
            message.textContent = `Lote seleccionado: ${next.dataset.label}`;
        } else {
            message.textContent = 'No hay lotes sin ubicacion en este filtro.';
        }
    });

    function coordinates(event) {
        const rect = image.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) {
            return null;
        }

        return {
            coord_x: Math.max(0, Math.min(100, ((event.clientX - rect.left) / rect.width) * 100)),
            coord_y: Math.max(0, Math.min(100, ((event.clientY - rect.top) / rect.height) * 100)),
        };
    }

    function createPoint(loteId, coords) {
        const option = selected.querySelector(`option[value="${loteId}"]`);
        const point = document.createElement('button');
        point.type = 'button';
        point.className = `map-point lot-point ${option?.dataset.estado || 'disponible'}`;
        point.dataset.loteId = loteId;
        point.dataset.label = option?.dataset.label || '';
        point.dataset.urbanizacion = '{{ $urbanizacion->nombre }}';
        point.dataset.manzano = option?.dataset.label?.split('-')[0] || '';
        point.dataset.lote = option?.dataset.label?.split('-').pop() || '';
        point.dataset.superficie = '';
        point.dataset.precio = '';
        point.dataset.cuotaInicial = option?.dataset.cuotaInicial || '';
        point.dataset.estado = option?.dataset.estado || 'disponible';
        point.dataset.detailUrl = option?.dataset.detailUrl || '';
        point.dataset.reservaUrl = option?.dataset.reservaUrl || '';
        point.dataset.ventaUrl = option?.dataset.ventaUrl || '';
        point.dataset.editUrl = option?.dataset.editUrl || '';
        point.dataset.canReservar = option?.dataset.canReservar || '0';
        point.dataset.canVender = option?.dataset.canVender || '0';
        point.dataset.canEditar = option?.dataset.canEditar || '0';
        point.title = option?.dataset.label || '';
        point.style.left = `${coords.coord_x}%`;
        point.style.top = `${coords.coord_y}%`;
        point.innerHTML = `<span>${(option?.dataset.label || '').split('-').pop()}</span>`;
        attachDrag(point);
        layer.appendChild(point);
        return point;
    }

    function selectedOption(loteId) {
        return selected?.querySelector(`option[value="${loteId}"]`);
    }

    async function savePosition(loteId, point, coords) {
        if (!loteId) {
            message.textContent = 'Selecciona primero un lote';
            return;
        }
        if (editLocked) {
            message.textContent = 'Edicion bloqueada. Desbloquea para mover puntos.';
            return;
        }
        if (!coords) {
            message.textContent = 'Haz clic dentro de la imagen del plano.';
            return;
        }

        const response = await fetch(`{{ url('/mapa/lotes') }}/${loteId}/posicion`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: JSON.stringify(coords),
        });

        if (!response.ok) {
            message.textContent = 'No se pudo guardar la posicion.';
            return;
        }

        const currentPoint = point || map.querySelector(`[data-lote-id="${loteId}"]`) || createPoint(loteId, coords);
        currentPoint.style.left = `${coords.coord_x}%`;
        currentPoint.style.top = `${coords.coord_y}%`;

        const option = selectedOption(loteId);
        if (option) {
            option.dataset.hasPosition = '1';
            option.textContent = option.textContent.replace(' (sin ubicacion)', '');
        }

        message.textContent = 'Lote ubicado correctamente';
    }

    async function clearPosition() {
        const loteId = selected?.value;
        if (!loteId) {
            message.textContent = 'Selecciona primero un lote';
            return;
        }

        const response = await fetch(`{{ url('/mapa/lotes') }}/${loteId}/posicion`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            message.textContent = 'No se pudo quitar la ubicacion.';
            return;
        }

        map.querySelector(`[data-lote-id="${loteId}"]`)?.remove();

        const option = selectedOption(loteId);
        if (option) {
            option.dataset.hasPosition = '0';
            if (!option.textContent.includes('(sin ubicacion)')) {
                option.textContent = `${option.textContent} (sin ubicacion)`;
            }
        }

        message.textContent = 'Ubicacion quitada correctamente';
    }

    document.getElementById('clear-position')?.addEventListener('click', clearPosition);

    map.addEventListener('mousemove', (event) => {
        if (!editMode || !mouseCoordinates) return;

        const coords = coordinates(event);
        mouseCoordinates.textContent = coords
            ? `X: ${coords.coord_x.toFixed(2)}%, Y: ${coords.coord_y.toFixed(2)}%`
            : 'X: --%, Y: --%';
    });

    map.addEventListener('click', (event) => {
        if (event.target.closest('.map-zoom-controls, .lot-modal, .modal-overlay, .edit-panel')) return;

        const point = event.target.closest('.map-point');

        if (!editMode) {
            if (!zoomController.hasPanMoved() && point) {
                lotModal?.open(point);
            }
            return;
        }

        if (!point) {
            savePosition(selected?.value, null, coordinates(event));
        }
    });

    function attachDrag(point) {
        point.addEventListener('pointerdown', (event) => {
            if (!editMode || editLocked) return;
            event.preventDefault();
            dragging = point;
            point.setPointerCapture(event.pointerId);
        });

        point.addEventListener('pointermove', (event) => {
            if (!editMode || editLocked || dragging !== point) return;
            const coords = coordinates(event);
            if (!coords) return;
            point.style.left = `${coords.coord_x}%`;
            point.style.top = `${coords.coord_y}%`;
        });

        point.addEventListener('pointerup', (event) => {
            if (!editMode || editLocked || dragging !== point) return;
            savePosition(point.dataset.loteId, point, coordinates(event));
            dragging = null;
        });
    }

    map.querySelectorAll('.map-point').forEach(attachDrag);
});
</script>
@endsection
