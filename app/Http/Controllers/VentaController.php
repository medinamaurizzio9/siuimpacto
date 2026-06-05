<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Venta;
use App\Services\SaleService;
use App\Services\AuditService;
use App\Services\CommercialAccessService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(Request $request, CommercialAccessService $access): View
    {
        $query = Venta::with('cliente', 'lote.manzano.urbanizacion', 'cuotas', 'vendedor', 'grupoComercial');
        $access->applyVentas($query, $request->user());

        return view('ventas.index', [
            'ventas' => $query->where('urbanizacion_id', UrbanizacionContext::currentId())->latest()->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        $venta = new Venta(['fecha_venta' => now(), 'numero_cuotas' => 12, 'estado' => 'activa']);
        if ($request->filled('cliente_id')) {
            $cliente = Cliente::findOrFail($request->integer('cliente_id'));
            abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
            $venta->cliente_id = $cliente->id;
        }
        if ($request->filled('lote_id')) {
            $venta->lote_id = $request->integer('lote_id');
        }

        return view('ventas.form', $this->formData($venta, $request));
    }

    public function store(StoreVentaRequest $request, SaleService $saleService): RedirectResponse
    {
        $lote = Lote::with('manzano')->findOrFail($request->validated('lote_id'));
        $cliente = Cliente::findOrFail($request->validated('cliente_id'));
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');

        $saleService->create($request->validated(), $request->user());

        return redirect()->route('ventas.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function edit(Venta $venta): View
    {
        abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');

        return view('ventas.form', $this->formData($venta, request()));
    }

    public function update(StoreVentaRequest $request, Venta $venta, AuditService $auditService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::ventaBelongsToCurrent($venta), 403, 'No tienes acceso a esta urbanizacion');
        $lote = Lote::with('manzano')->findOrFail($request->validated('lote_id'));
        $cliente = Cliente::findOrFail($request->validated('cliente_id'));
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');

        $before = $venta->toArray();
        $editable = $request->safe()->except(['metodo_pago', 'referencia', 'admin_confirma_reserva']);
        if (! $request->user()->hasRole('super administrador')) {
            $editable = collect($editable)->except(['grupo_comercial_id', 'supervisor_comercial_id', 'supervisor_ventas_id', 'vendedor_id'])->all();
        }
        $venta->update([
            ...$editable,
            'usuario_actualizador_id' => $request->user()->id,
            'tipo_venta' => $request->integer('numero_cuotas') > 0 ? 'credito' : 'contado',
            'monto_total' => $request->input('precio_final'),
        ]);
        $auditService->log($venta, 'editar_venta', 'Venta actualizada.', $before, $venta->fresh()->toArray(), $request);

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

    private function formData(Venta $venta, Request $request): array
    {
        $isSuperAdmin = $request->user()->hasRole('super administrador');

        return [
            'venta' => $venta,
            'clientes' => UrbanizacionContext::clientes(Cliente::query())->orderBy('nombre')->get(),
            'lotes' => UrbanizacionContext::lotes(Lote::with('manzano.urbanizacion', 'reservaActiva.cliente'))
                ->where(fn ($query) => $query->whereIn('estado', ['disponible', 'reservado'])->orWhere('id', $venta->lote_id))
                ->orderBy('codigo')
                ->get(),
            'grupos' => $isSuperAdmin ? \App\Models\GrupoComercial::where('activo', true)->orderBy('nombre')->get() : collect(),
            'supervisoresComerciales' => $isSuperAdmin ? \App\Models\User::role('supervisor comercial')->orderBy('name')->get() : collect(),
            'supervisoresVentas' => $isSuperAdmin ? \App\Models\User::role(['supervisor ventas', 'supervisor'])->orderBy('name')->get() : collect(),
            'vendedores' => $isSuperAdmin ? \App\Models\User::role('vendedor')->orderBy('name')->get() : collect(),
            'isSuperAdmin' => $isSuperAdmin,
        ];
    }
}
