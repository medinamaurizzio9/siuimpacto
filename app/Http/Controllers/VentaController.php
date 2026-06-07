<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Venta;
use App\Services\SaleService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(): View
    {
        return view('ventas.index', [
            'ventas' => UrbanizacionContext::ventas(Venta::with('cliente', 'lote.manzano.urbanizacion', 'cuotas'))->latest()->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        abort_if($request->user()->hasAnyRole(['vendedor', 'supervisor']), 403, 'Los asesores solo pueden crear reservas. No tienen permiso para registrar ventas.');

        $venta = new Venta(['fecha_venta' => now(), 'numero_cuotas' => 12, 'estado' => 'activa']);
        if ($request->filled('cliente_id')) {
            $cliente = Cliente::findOrFail($request->integer('cliente_id'));
            abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
            $venta->cliente_id = $cliente->id;
        }
        if ($request->filled('lote_id')) {
            $venta->lote_id = $request->integer('lote_id');
        }

        return view('ventas.form', $this->formData($venta));
    }

    public function store(StoreVentaRequest $request, SaleService $saleService): RedirectResponse
    {
        abort_if($request->user()->hasAnyRole(['vendedor', 'supervisor']), 403, 'Los asesores solo pueden crear reservas. No tienen permiso para registrar ventas.');

        $lote = Lote::with('manzano')->findOrFail($request->validated('lote_id'));
        $cliente = Cliente::findOrFail($request->validated('cliente_id'));
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');

        $saleService->create($request->validated(), $request->user());

        return redirect()->route('ventas.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function edit(Request $request, Venta $venta): View
    {
        $this->authorizeEdit($request, $venta);
        abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');

        return view('ventas.form', $this->formData($venta));
    }

    public function update(StoreVentaRequest $request, Venta $venta, SaleService $saleService): RedirectResponse
    {
        $this->authorizeEdit($request, $venta);
        abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');
        $lote = Lote::with('manzano')->findOrFail($request->validated('lote_id'));
        $cliente = Cliente::findOrFail($request->validated('cliente_id'));
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');

        $saleService->update($venta, $request->validated(), $request->user(), $request->string('motivo_cambio')->toString());

        return redirect()->route('ventas.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function destroy(Request $request, Venta $venta, SaleService $saleService): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para anular ventas.');
        abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate(['motivo' => ['required', 'string', 'max:500']]);
        $saleService->annul($venta, $request->user(), $data['motivo']);

        return back()->with('status', 'Operacion realizada correctamente.');
    }

    private function formData(Venta $venta): array
    {
        $venta->loadMissing('cashMovements');

        return [
            'venta' => $venta,
            'initialMovement' => $venta->cashMovements
                ->whereNull('installment_id')
                ->whereIn('concepto', ['anticipo', 'contado'])
                ->where('estado', 'confirmado')
                ->first(),
            'clientes' => UrbanizacionContext::clientes(Cliente::query())->orderBy('nombre')->get(),
            'lotes' => UrbanizacionContext::lotes(Lote::with('manzano.urbanizacion', 'reservaActiva.cliente'))
                ->where(fn ($query) => $query->whereIn('estado', ['disponible', 'reservado'])->orWhere('id', $venta->lote_id))
                ->orderBy('codigo')
                ->get(),
        ];
    }

    private function authorizeEdit(Request $request, Venta $venta): void
    {
        abort_unless(
            $request->user()->hasRole('administrador') && $request->user()->can('editar ventas'),
            403,
            'No tienes permiso para editar esta venta.'
        );

        abort_if(
            $venta->estado === 'anulada' && ! $request->user()->can('editar ventas anuladas'),
            403,
            'No tienes permiso especial para editar una venta anulada.'
        );
    }
}
