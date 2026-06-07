@extends('layouts.app')

@section('content')
<div class="topbar">
    <h1 class="title">Caja</h1>
    @can('exportar reportes')
        <a class="btn secondary" href="{{ route('export.csv', ['tipo' => 'caja'] + request()->query()) }}">Exportar CSV</a>
    @endcan
</div>

@if (session('status'))
    <div class="status">{{ session('status') }}</div>
@endif

<form method="GET" action="{{ route('caja.index') }}" class="card filter-form cash-filters">
    <div class="field">
        <label for="q">Buscar movimiento</label>
        <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cliente, documento, concepto o referencia">
    </div>

    <div class="field">
        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo">
            <option value="">Todos</option>
            @foreach(['ingreso', 'egreso'] as $tipo)
                <option value="{{ $tipo }}" @selected(($filters['tipo'] ?? '') === $tipo)>{{ ucfirst($tipo) }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="concepto">Concepto</label>
        <select id="concepto" name="concepto">
            <option value="">Todos</option>
            @foreach(['reserva', 'anticipo', 'cuota', 'contado', 'otro'] as $concepto)
                <option value="{{ $concepto }}" @selected(($filters['concepto'] ?? '') === $concepto)>{{ ucfirst($concepto) }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="metodo_pago">Metodo</label>
        <select id="metodo_pago" name="metodo_pago">
            <option value="">Todos</option>
            @foreach(['efectivo', 'QR', 'banco', 'transferencia', 'otro'] as $metodo)
                <option value="{{ $metodo }}" @selected(($filters['metodo_pago'] ?? '') === $metodo)>{{ ucfirst($metodo) }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="estado">Estado</label>
        <select id="estado" name="estado">
            <option value="">Todos</option>
            @foreach(['confirmado', 'anulado'] as $estado)
                <option value="{{ $estado }}" @selected(($filters['estado'] ?? '') === $estado)>{{ ucfirst($estado) }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="fecha_desde">Desde</label>
        <input id="fecha_desde" type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}">
    </div>

    <div class="field">
        <label for="fecha_hasta">Hasta</label>
        <input id="fecha_hasta" type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}">
    </div>

    <div class="field">
        <label for="per_page">Mostrar</label>
        <select id="per_page" name="per_page">
            @foreach([15, 30, 50, 100] as $size)
                <option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 15) === $size)>{{ $size }} por pagina</option>
            @endforeach
        </select>
    </div>

    <div class="filter-actions">
        <button class="btn" type="submit">Filtrar</button>
        <a class="btn secondary" href="{{ route('caja.index') }}">Limpiar</a>
    </div>
</form>

<div class="list-summary">
    Mostrando {{ $movimientos->firstItem() ?? 0 }}-{{ $movimientos->lastItem() ?? 0 }} de {{ $movimientos->total() }} movimientos
</div>

<div class="table-scroll">
    <table class="table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Metodo</th>
                <th>Referencia</th>
                <th>Monto</th>
                <th>Estado</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $movimiento)
                <tr>
                    <td>{{ $movimiento->fecha->format('d/m/Y') }}</td>
                    <td>{{ $movimiento->cliente?->nombre }}</td>
                    <td>{{ $movimiento->tipo }}</td>
                    <td>{{ $movimiento->concepto }}</td>
                    <td>{{ $movimiento->metodo_pago }}</td>
                    <td>{{ $movimiento->referencia ?: '-' }}</td>
                    <td>{{ number_format($movimiento->monto, 2) }}</td>
                    <td><span class="badge {{ $movimiento->estado }}">{{ $movimiento->estado }}</span></td>
                    <td class="actions">
                        <a class="btn secondary" href="{{ route('pdf.recibo', $movimiento) }}" target="_blank" rel="noopener">Imprimir recibo</a>
                        @can('anular caja')
                            @if($movimiento->estado !== 'anulado')
                                <form method="POST" action="{{ route('caja.annul', $movimiento) }}" onsubmit="const m = prompt('Motivo obligatorio de anulacion'); if(!m) return false; this.motivo.value=m; return confirm('Confirma que deseas anular este movimiento de caja?');">
                                    @csrf
                                    <input type="hidden" name="motivo">
                                    <button class="btn danger">Anular</button>
                                </form>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty-table">No se encontraron movimientos con los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">{{ $movimientos->links() }}</div>
@endsection
