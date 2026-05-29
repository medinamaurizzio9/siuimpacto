@php
    $selectedCliente = $selectedCliente ?? null;
    $inputName = $inputName ?? 'cliente_id';
    $loteSource = $loteSource ?? 'lote_id';
@endphp

<div class="field cliente-search" data-cliente-search data-search-url="{{ route('clientes.buscar') }}" data-create-url="{{ route('clientes.create') }}" data-lote-source="{{ $loteSource }}">
    <label>Cliente</label>
    <input type="hidden" name="{{ $inputName }}" data-cliente-id value="{{ old($inputName, $selectedCliente?->id) }}">
    <input type="search" data-cliente-query placeholder="Buscar cliente por nombre, carnet o celular" autocomplete="off" value="{{ $selectedCliente?->nombre }}">
    <div class="cliente-search-results" data-cliente-results hidden></div>
    <div class="cliente-selected-card" data-cliente-card @if(! $selectedCliente) hidden @endif>
        <strong data-cliente-card-nombre>{{ $selectedCliente?->nombre }}</strong>
        <span data-cliente-card-documento>{{ $selectedCliente?->documento }}</span>
        <span data-cliente-card-contacto>{{ trim(($selectedCliente?->telefono ?? '').' '.($selectedCliente?->email ?? '')) }}</span>
    </div>
    <div class="muted" data-cliente-empty hidden>
        No se encontro cliente.
        <a href="{{ route('clientes.create') }}" data-cliente-create>Crear nuevo cliente/interesado</a>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-cliente-search]').forEach((root) => {
        if (root.dataset.ready === '1') return;
        root.dataset.ready = '1';

        const query = root.querySelector('[data-cliente-query]');
        const hidden = root.querySelector('[data-cliente-id]');
        const results = root.querySelector('[data-cliente-results]');
        const empty = root.querySelector('[data-cliente-empty]');
        const createLink = root.querySelector('[data-cliente-create]');
        const card = root.querySelector('[data-cliente-card]');
        const cardNombre = root.querySelector('[data-cliente-card-nombre]');
        const cardDocumento = root.querySelector('[data-cliente-card-documento]');
        const cardContacto = root.querySelector('[data-cliente-card-contacto]');
        const loteSourceName = root.dataset.loteSource || 'lote_id';
        let timer = null;

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
            hidden.value = cliente.id;
            query.value = cliente.nombre;
            cardNombre.textContent = cliente.nombre || '';
            cardDocumento.textContent = cliente.documento || '';
            cardContacto.textContent = [cliente.telefono, cliente.email].filter(Boolean).join(' ');
            card.hidden = false;
            results.hidden = true;
            empty.hidden = true;
        }

        function render(clientes) {
            results.innerHTML = '';
            if (!clientes.length) {
                results.hidden = true;
                empty.hidden = false;
                updateCreateLink();
                return;
            }

            empty.hidden = true;
            clientes.forEach((cliente) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'cliente-search-result';
                button.innerHTML = `<strong>${cliente.nombre || ''}</strong><span>${cliente.documento || ''} ${cliente.telefono || ''}</span>`;
                button.addEventListener('click', () => selectCliente(cliente));
                results.appendChild(button);
            });
            results.hidden = false;
        }

        query.addEventListener('input', () => {
            hidden.value = '';
            card.hidden = true;
            window.clearTimeout(timer);

            const q = query.value.trim();
            if (q.length < 2) {
                results.hidden = true;
                empty.hidden = true;
                return;
            }

            timer = window.setTimeout(async () => {
                const url = new URL(root.dataset.searchUrl, window.location.origin);
                url.searchParams.set('q', q);
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                render(response.ok ? await response.json() : []);
            }, 220);
        });

        document.querySelector(`[name="${loteSourceName}"]`)?.addEventListener('change', updateCreateLink);
        updateCreateLink();
    });
});
</script>
@endonce
