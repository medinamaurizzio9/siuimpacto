window.createImpactoMapZoom = function createImpactoMapZoom(options) {
    const map = options.map;
    const layer = options.layer;
    if (!map || !layer) return null;
    if (map._impactoMapZoom) return map._impactoMapZoom;

    console.log('map zoom loaded');

    const zoomLabel = options.zoomLabel;
    const canPan = options.canPan || (() => true);
    const shouldIgnorePanTarget = options.shouldIgnorePanTarget || (() => false);
    const minZoom = 0.5;
    const maxZoom = 5;
    const step = 0.25;
    const pointers = new Map();

    let zoom = 1;
    let panX = 0;
    let panY = 0;
    let panStart = null;
    let pinchStart = null;
    let raf = null;
    let panMoved = false;

    function clampZoom(value) {
        return Math.max(minZoom, Math.min(maxZoom, value));
    }

    function scheduleRender() {
        if (raf) return;
        raf = window.requestAnimationFrame(() => {
            raf = null;
            layer.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
            layer.classList.toggle('zoom-labels-hidden', zoom < 0.7);
            map.classList.toggle('pannable', canPan());
            if (zoomLabel) zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
        });
    }

    function zoomAt(nextZoom, clientX, clientY) {
        const newZoom = clampZoom(nextZoom);
        const rect = map.getBoundingClientRect();
        const originX = clientX - rect.left;
        const originY = clientY - rect.top;
        const contentX = (originX - panX) / zoom;
        const contentY = (originY - panY) / zoom;

        panX = originX - contentX * newZoom;
        panY = originY - contentY * newZoom;
        zoom = newZoom;

        if (zoom === 1) {
            panX = 0;
            panY = 0;
        }

        scheduleRender();
    }

    function zoomBy(delta) {
        const rect = map.getBoundingClientRect();
        zoomAt(zoom + delta, rect.left + rect.width / 2, rect.top + rect.height / 2);
    }

    function reset() {
        zoom = 1;
        panX = 0;
        panY = 0;
        scheduleRender();
    }

    function pointerDistance(items) {
        const [a, b] = items;
        return Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY);
    }

    function pointerCenter(items) {
        const [a, b] = items;
        return {
            clientX: (a.clientX + b.clientX) / 2,
            clientY: (a.clientY + b.clientY) / 2,
        };
    }

    map.addEventListener('wheel', (event) => {
        event.preventDefault();
        zoomAt(zoom + (event.deltaY < 0 ? step : -step), event.clientX, event.clientY);
    }, { passive: false });

    map.addEventListener('pointerdown', (event) => {
        if (!canPan() || shouldIgnorePanTarget(event.target)) return;

        event.preventDefault();
        pointers.set(event.pointerId, { clientX: event.clientX, clientY: event.clientY });
        map.setPointerCapture(event.pointerId);
        map.classList.add('panning');
        panMoved = false;

        if (pointers.size === 1) {
            panStart = { pointerId: event.pointerId, startX: event.clientX, startY: event.clientY, panX, panY };
            pinchStart = null;
        }

        if (pointers.size === 2) {
            const items = [...pointers.values()];
            pinchStart = {
                distance: pointerDistance(items),
                zoom,
                center: pointerCenter(items),
            };
            panStart = null;
        }
    });

    map.addEventListener('pointermove', (event) => {
        if (!pointers.has(event.pointerId)) return;

        pointers.set(event.pointerId, { clientX: event.clientX, clientY: event.clientY });

        if (pointers.size === 2 && pinchStart) {
            const items = [...pointers.values()];
            const distance = pointerDistance(items);
            const center = pointerCenter(items);
            zoomAt(pinchStart.zoom * (distance / pinchStart.distance), center.clientX, center.clientY);
            panMoved = true;
            return;
        }

        if (pointers.size === 1 && panStart && panStart.pointerId === event.pointerId) {
            panX = panStart.panX + event.clientX - panStart.startX;
            panY = panStart.panY + event.clientY - panStart.startY;
            panMoved = Math.abs(event.clientX - panStart.startX) + Math.abs(event.clientY - panStart.startY) > 3;
            scheduleRender();
        }
    });

    function endPointer(event) {
        pointers.delete(event.pointerId);
        if (pointers.size === 0) {
            panStart = null;
            pinchStart = null;
            map.classList.remove('panning');
            window.setTimeout(() => { panMoved = false; }, 0);
            return;
        }

        if (pointers.size === 1) {
            const [remainingId, remaining] = [...pointers.entries()][0];
            panStart = { pointerId: remainingId, startX: remaining.clientX, startY: remaining.clientY, panX, panY };
            pinchStart = null;
        }
    }

    map.addEventListener('pointerup', endPointer);
    map.addEventListener('pointercancel', endPointer);

    options.zoomIn?.addEventListener('click', () => zoomBy(step));
    options.zoomOut?.addEventListener('click', () => zoomBy(-step));
    options.reset?.addEventListener('click', reset);
    options.fullscreen?.addEventListener('click', () => map.closest('.map-shell')?.requestFullscreen?.() || map.requestFullscreen?.());

    scheduleRender();

    map._impactoMapZoom = {
        reset,
        zoomBy,
        zoomAt,
        hasPanMoved: () => panMoved,
        getZoom: () => zoom,
    };

    return map._impactoMapZoom;
};

window.createImpactoLotModal = function createImpactoLotModal(options = {}) {
    const modal = options.modal || document.getElementById('lote-map-modal');
    const overlay = options.overlay || document.getElementById('lotModalOverlay');
    const closeButton = options.closeButton || document.getElementById('lotModalClose');

    if (!modal) {
        return {
            open: () => {},
            close: () => {},
        };
    }

    const fields = {
        title: modal.querySelector('[data-modal-title]'),
        urbanizacion: modal.querySelector('[data-modal-urbanizacion]'),
        manzano: modal.querySelector('[data-modal-manzano]'),
        lote: modal.querySelector('[data-modal-lote]'),
        superficie: modal.querySelector('[data-modal-superficie]'),
        precio: modal.querySelector('[data-modal-precio]'),
        precioBs: modal.querySelector('[data-modal-precio-bs]'),
        cuotaInicial: modal.querySelector('[data-modal-cuota-inicial]'),
        cuotaInicialBs: modal.querySelector('[data-modal-cuota-inicial-bs]'),
        estado: modal.querySelector('[data-modal-estado]'),
        message: modal.querySelector('[data-modal-message]'),
    };

    const links = {
        detalle: modal.querySelector('[data-modal-link="detalle"]'),
        reservar: modal.querySelector('[data-modal-link="reservar"]'),
        vender: modal.querySelector('[data-modal-link="vender"]'),
        editar: modal.querySelector('[data-modal-link="editar"]'),
    };

    function write(field, value) {
        if (field) field.textContent = value || '';
    }

    function setLink(link, url, visible) {
        if (!link) return;

        const shouldShow = Boolean(url) && visible;
        link.href = shouldShow ? url : '#';
        link.hidden = !shouldShow;
        link.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
        link.style.pointerEvents = shouldShow ? 'auto' : 'none';
    }

    function close() {
        console.log('modal button clicked');
        modal.classList.add('hidden');
        overlay?.classList.add('hidden');
        document.body.classList.remove('map-modal-open');
        modal.setAttribute('aria-hidden', 'true');
        overlay?.setAttribute('aria-hidden', 'true');

        Object.values(links).forEach((link) => {
            if (link) link.href = '#';
        });
    }

    function open(point) {
        if (!point) return;

        write(fields.title, `Lote ${point.dataset.label || ''}`);
        write(fields.urbanizacion, point.dataset.urbanizacion);
        write(fields.manzano, point.dataset.manzano);
        write(fields.lote, point.dataset.lote);
        write(fields.superficie, point.dataset.superficie);
        write(fields.precio, point.dataset.precio);
        write(fields.precioBs, point.dataset.precioBs);
        write(fields.cuotaInicial, point.dataset.cuotaInicial);
        write(fields.cuotaInicialBs, point.dataset.cuotaInicialBs);
        write(fields.estado, point.dataset.estado);

        if (fields.estado) {
            fields.estado.className = `badge ${point.dataset.estado || ''}`;
        }

        setLink(links.detalle, point.dataset.detailUrl, true);
        setLink(links.reservar, point.dataset.reservaUrl, point.dataset.canReservar === '1');
        setLink(links.vender, point.dataset.ventaUrl, point.dataset.canVender === '1');
        setLink(links.editar, point.dataset.editUrl, point.dataset.canEditar === '1');

        const hasAction = point.dataset.canReservar === '1'
            || point.dataset.canVender === '1'
            || point.dataset.canEditar === '1';

        if (fields.message) {
            fields.message.hidden = hasAction || point.dataset.estado === 'disponible';
            fields.message.textContent = hasAction || point.dataset.estado === 'disponible'
                ? ''
                : 'Este lote no tiene acciones disponibles para tu rol o estado actual.';
        }

        overlay?.classList.remove('hidden');
        modal.classList.remove('hidden');
        document.body.classList.add('map-modal-open');
        overlay?.setAttribute('aria-hidden', 'false');
        modal.setAttribute('aria-hidden', 'false');
    }

    closeButton?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    Object.values(links).forEach((link) => {
        link?.addEventListener('click', () => console.log('modal button clicked'));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });

    return { open, close };
};
