@php
    $selectedCliente = $selectedCliente ?? null;
    $inputName = $inputName ?? 'cliente_id';
    $loteSource = $loteSource ?? 'lote_id';
@endphp

<div class="field cliente-search" data-cliente-search data-search-url="{{ route('clientes.buscar') }}" data-create-url="{{ route('clientes.create') }}" data-lote-source="{{ $loteSource }}">
    <label for="cliente_search">Cliente</label>
    <input type="text" id="cliente_search" name="cliente_search" data-cliente-query placeholder="Buscar cliente por nombre, carnet o celular" autocomplete="off" value="{{ $selectedCliente?->nombre }}">
    <input type="hidden" id="cliente_id" name="{{ $inputName }}" data-cliente-id value="{{ $selectedCliente?->id }}">

    <div id="cliente_results" class="cliente-search-results" data-cliente-results hidden style="display:none"></div>

    <div id="cliente_selected_card" class="cliente-selected-card" data-cliente-card style="display:{{ $selectedCliente ? 'block' : 'none' }}">
        <strong id="cliente_selected_nombre" data-cliente-card-nombre>{{ $selectedCliente?->nombre }}</strong>
        <div class="cliente-selected-detail">Documento: <span id="cliente_selected_documento" data-cliente-card-documento>{{ $selectedCliente?->documento ?: 'Sin documento' }}</span></div>
        <div class="cliente-selected-detail">Teléfono: <span id="cliente_selected_telefono" data-cliente-card-telefono>{{ $selectedCliente?->telefono ?: 'Sin teléfono' }}</span></div>
        <div class="cliente-selected-detail">Email: <span id="cliente_selected_email" data-cliente-card-email>{{ $selectedCliente?->email ?: 'Sin email' }}</span></div>
    </div>

    <div class="cliente-validation-error" data-cliente-validation hidden>Debe seleccionar un cliente válido de la lista.</div>
    <div class="muted" data-cliente-empty hidden>
        No se encontró cliente.
        <a href="{{ route('clientes.create') }}" data-cliente-create>Crear nuevo cliente/interesado</a>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-cliente-search]').forEach((root) => {
        if (root.dataset.ready === '1') return;
        root.dataset.ready = '1';

        const form = root.closest('form');
        const query = root.querySelector('[data-cliente-query]');
        const hidden = root.querySelector('[data-cliente-id]');
        const results = root.querySelector('[data-cliente-results]');
        const empty = root.querySelector('[data-cliente-empty]');
        const validation = root.querySelector('[data-cliente-validation]');
        const createLink = root.querySelector('[data-cliente-create]');
        const card = root.querySelector('[data-cliente-card]');
        const cardNombre = root.querySelector('[data-cliente-card-nombre]');
        const cardDocumento = root.querySelector('[data-cliente-card-documento]');
        const cardTelefono = root.querySelector('[data-cliente-card-telefono]');
        const cardEmail = root.querySelector('[data-cliente-card-email]');
        const loteSourceName = root.dataset.loteSource || 'lote_id';
        let timer = null;
        let searchController = null;

        function closeResults() {
            results.innerHTML = '';
            results.hidden = true;
            results.style.display = 'none';
        }

        function currentCreateUrl() {
            const lote = document.querySelector(`[name="${loteSourceName}"]`)?.value;
            const url = new URL(root.dataset.createUrl, window.location.origin);
            if (lote) url.searchParams.set('lote_id', lote);
            return url.toString();
        }

        function updateCreateLink() {
            if (createLink) createLink.href = currentCreateUrl();
        }

        function selectCliente(cliente) {
            searchController?.abort();
            hidden.value = cliente.id;
            query.value = cliente.nombre || '';
            cardNombre.textContent = cliente.nombre || '';
            cardDocumento.textContent = cliente.documento || 'Sin documento';
            cardTelefono.textContent = cliente.telefono || 'Sin teléfono';
            cardEmail.textContent = cliente.email || 'Sin email';
            card.style.display = 'block';
            empty.hidden = true;
            validation.hidden = true;
            closeResults();
        }

        function render(clientes) {
            closeResults();
            if (!clientes.length) {
                empty.hidden = false;
                updateCreateLink();
                return;
            }

            empty.hidden = true;
            clientes.slice(0, 10).forEach((cliente) => {
                const button = document.createElement('button');
                const nombre = document.createElement('strong');
                const detalle = document.createElement('span');

                button.type = 'button';
                button.className = 'cliente-search-result';
                nombre.textContent = cliente.nombre || '';
                detalle.textContent = [cliente.documento || 'Sin documento', cliente.telefono || 'Sin teléfono'].join(' · ');
                button.append(nombre, detalle);
                button.addEventListener('click', () => selectCliente(cliente));
                results.appendChild(button);
            });
            results.hidden = false;
            results.style.display = 'block';
        }

        query.addEventListener('input', () => {
            hidden.value = '';
            card.style.display = 'none';
            validation.hidden = true;
            empty.hidden = true;
            window.clearTimeout(timer);
            searchController?.abort();

            const q = query.value.trim();
            if (q.length < 2) {
                closeResults();
                return;
            }

            timer = window.setTimeout(async () => {
                searchController = new AbortController();
                const url = new URL(root.dataset.searchUrl, window.location.origin);
                url.searchParams.set('q', q);

                try {
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json' },
                        signal: searchController.signal,
                    });
                    render(response.ok ? await response.json() : []);
                } catch (error) {
                    if (error.name !== 'AbortError') render([]);
                }
            }, 220);
        });

        form?.addEventListener('submit', (event) => {
            if (hidden.value) return;

            event.preventDefault();
            validation.hidden = false;
            closeResults();
            query.focus();
        });

        document.addEventListener('click', (event) => {
            if (!root.contains(event.target)) closeResults();
        });

        document.querySelector(`[name="${loteSourceName}"]`)?.addEventListener('change', updateCreateLink);
        updateCreateLink();
    });
});
</script>
@endonce
