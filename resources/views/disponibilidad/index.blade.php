<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Disponibilidad - IMPACTO URBANIZACIONES</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<main class="public-page">
    <div class="public-header">
        <div>
            <h1>IMPACTO URBANIZACIONES</h1>
            <p>Sistema Integral de Terrenos</p>
        </div>
        <a class="btn" href="#consulta">Consultar con asesor</a>
    </div>

    <form method="GET" action="{{ route('disponibilidad.publica') }}" class="card form public-filter">
        <div class="field">
            <label>Urbanizacion</label>
            <select name="urbanizacion_id" onchange="this.form.submit()">
                @foreach($urbanizaciones as $item)
                    <option value="{{ $item->id }}" @selected($urbanizacion?->id === $item->id)>{{ $item->nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label>&nbsp;</label><button class="btn" type="submit">Ver disponibilidad</button></div>
    </form>

    @if($urbanizacion)
        @php
            $lotes = $urbanizacion->manzanos->flatMap->lotes;
            $locatedLotes = $lotes->filter(fn ($lote) => ! is_null($lote->coord_x) && ! is_null($lote->coord_y));
            $counts = $lotes->countBy('estado');
        @endphp

        <section class="card">
            <div class="map-toolbar">
                <div>
                    <h2>{{ $urbanizacion->nombre }}</h2>
                    <p class="muted">{{ $urbanizacion->ubicacion }}</p>
                </div>
                <div class="legend">
                    @foreach(['disponible' => 'Disponible', 'vendido' => 'Vendido', 'reservado' => 'Reservado', 'bloqueado' => 'Bloqueado'] as $key => $label)
                        <span><i class="legend-dot {{ $key }}"></i>{{ $label }}: {{ $counts[$key] ?? 0 }}</span>
                    @endforeach
                </div>
            </div>

            @if($urbanizacion->plano_imagen)
                <p class="muted map-help">Las posiciones se guardan proporcionalmente, por eso se mantienen en celular y escritorio.</p>
                <div class="map-shell">
                    <div class="map-zoom-controls">
                        <button class="btn secondary" type="button" data-zoom-in title="Acercar" aria-label="Acercar">Zoom +</button>
                        <button class="btn secondary" type="button" data-zoom-out title="Alejar" aria-label="Alejar">Zoom -</button>
                        <button class="btn secondary" type="button" data-zoom-reset title="Restablecer vista" aria-label="Restablecer vista">Restablecer</button>
                        <button class="btn secondary" type="button" data-zoom-fullscreen title="Pantalla completa" aria-label="Pantalla completa">Pantalla completa</button>
                        <span class="zoom-value" data-zoom-value>100%</span>
                    </div>
                    <div class="plan-map-viewport" id="public-plan-map">
                        <div class="plan-map-layer" id="public-plan-map-layer">
                            <img class="plan-map-image" src="{{ asset('storage/'.$urbanizacion->plano_imagen) }}" alt="Plano {{ $urbanizacion->nombre }}">
                            @foreach($locatedLotes as $lote)
                                <span class="map-point lot-point public {{ $lote->estado }}" style="left: {{ max(0, min(100, (float) $lote->coord_x)) }}%; top: {{ max(0, min(100, (float) $lote->coord_y)) }}%;"><span>{{ $lote->codigo }}</span></span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-plan">Esta urbanizacion aun no tiene plano cargado</div>
            @endif
        </section>

        <section class="card" style="margin-top:18px;">
            <h2>Lotes disponibles</h2>
            <table class="table">
                <thead><tr><th>Manzano</th><th>Lote</th><th>Superficie</th>@if($urbanizacion->mostrar_precio_publico)<th>Precio</th>@endif<th>Estado</th><th></th></tr></thead>
                <tbody>
                @foreach($lotes->where('estado', 'disponible')->sortBy([['manzano.codigo', 'asc'], ['codigo', 'asc']]) as $lote)
                    <tr>
                        <td>{{ $lote->manzano->codigo }}</td>
                        <td>{{ $lote->codigo }}</td>
                        <td>{{ number_format($lote->superficie, 2) }} m2</td>
                        @if($urbanizacion->mostrar_precio_publico)<td>{{ number_format($lote->precio, 2) }}</td>@endif
                        <td><span class="badge {{ $lote->estado }}">{{ $lote->estado }}</span></td>
                        <td>@if($lote->estado === 'disponible')<a class="btn secondary" href="#consulta">Consultar con asesor</a>@endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>

        <section id="consulta" class="card public-cta">
            <h2>Consultar con asesor</h2>
            <p class="muted">Escribenos indicando urbanizacion, manzano y lote de interes para confirmar disponibilidad actual.</p>
            <a class="btn" href="mailto:ventas@impacto.test?subject=Consulta%20de%20lote%20{{ urlencode($urbanizacion->nombre) }}">Consultar con asesor</a>
        </section>
    @else
        <div class="card">No hay urbanizaciones disponibles.</div>
    @endif
</main>
<script src="{{ asset('js/map-zoom.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const map = document.getElementById('public-plan-map');
    const layer = document.getElementById('public-plan-map-layer');
    if (!map || !layer || typeof window.createImpactoMapZoom !== 'function') return;

    const shell = map.closest('.map-shell');
    window.createImpactoMapZoom({
        map,
        layer,
        zoomIn: shell?.querySelector('[data-zoom-in]'),
        zoomOut: shell?.querySelector('[data-zoom-out]'),
        reset: shell?.querySelector('[data-zoom-reset]'),
        fullscreen: shell?.querySelector('[data-zoom-fullscreen]'),
        zoomLabel: shell?.querySelector('[data-zoom-value]'),
    });
});
</script>
</body>
</html>
