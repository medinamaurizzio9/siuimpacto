@extends('layouts.app')
@section('content')
<div class="topbar"><h1 class="title">Urbanizaciones</h1><a class="btn" href="{{ route('urbanizaciones.create') }}">Nueva</a></div>
@if (session('status')) <div class="status">{{ session('status') }}</div> @endif
<table class="table">
    <thead><tr><th>Nombre</th><th>Propietario</th><th>Ubicacion</th><th>Superficie</th><th>Manzanos</th><th>Terrenos</th><th>Estado</th><th>Link publico</th><th>Acciones</th></tr></thead>
    <tbody>
    @foreach ($urbanizaciones as $urbanizacion)
        @php($publicLink = $urbanizacion->slug ? app(\App\Services\PublicUrlService::class)->route('disponibilidad.urbanizacion', ['slug' => $urbanizacion->slug]) : null)
        <tr>
            <td>{{ $urbanizacion->nombre }}</td><td>{{ $urbanizacion->propietario ?: 'Sin registrar' }}</td><td>{{ $urbanizacion->ubicacion }}</td><td>{{ number_format($urbanizacion->superficie_total, 2) }}</td><td>{{ $urbanizacion->manzanos_count }}</td><td>{{ $urbanizacion->total_lotes }}</td><td>{{ $urbanizacion->estado }}</td>
            <td>
                @if($publicLink)
                    <div class="public-link-cell">
                        <a href="{{ $publicLink }}" target="_blank" rel="noopener">{{ $publicLink }}</a>
                        <button class="btn secondary" type="button" data-copy-link="{{ $publicLink }}">Copiar link publico</button>
                    </div>
                @else
                    Sin link
                @endif
            </td>
            <td class="actions"><a class="btn secondary" href="{{ route('urbanizaciones.edit', $urbanizacion) }}">Editar</a><form method="POST" action="{{ route('urbanizaciones.destroy', $urbanizacion) }}">@csrf @method('DELETE')<button class="btn danger">Eliminar</button></form></td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="pagination">{{ $urbanizaciones->links() }}</div>
<script>
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-link]');
    if (!button) return;

    await navigator.clipboard.writeText(button.dataset.copyLink);
    button.textContent = 'Copiado';
    setTimeout(() => button.textContent = 'Copiar link publico', 1600);
});
</script>
@endsection
