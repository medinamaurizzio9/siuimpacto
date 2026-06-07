<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservaRequest;
use App\Models\Cliente;
use App\Models\Lote;
use App\Models\Reserva;
use App\Services\AuditService;
use App\Services\CommercialSettingsService;
use App\Services\LotService;
use App\Services\ReservationService;
use App\Services\ReservationVisibilityService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservaController extends Controller
{
    public function index(Request $request, ReservationVisibilityService $visibility): View
    {
        $query = UrbanizacionContext::reservas(Reserva::with('cliente', 'lote.manzano.urbanizacion', 'usuario', 'cashMovements'))->latest();
        $visibility->apply($query, $request->user());

        $this->applyFilters($query, $request, $visibility);

        return view('reservas.index', [
            'reservas' => $query->paginate(15)->withQueryString(),
            'vendedores' => $visibility->vendedores($request->user()),
            'tiposOperacion' => Reserva::TIPOS_OPERACION,
        ]);
    }

    private function applyFilters($query, Request $request, ReservationVisibilityService $visibility): void
    {
        if ($request->filled('estado')) {
            $query->where('estado', $request->query('estado'));
        }

        if ($request->filled('tipo_operacion')) {
            $query->where('tipo_operacion', $request->query('tipo_operacion'));
        }

        if ($request->filled('usuario_id') && $visibility->canFilterUser($request->user(), $request->integer('usuario_id'))) {
            $query->where('usuario_id', $request->integer('usuario_id'));
        }

        if ($request->filled('cliente')) {
            $query->whereHas('cliente', fn ($builder) => $builder->where('nombre', 'like', '%'.$request->query('cliente').'%'));
        }

        if ($request->filled('documento')) {
            $query->whereHas('cliente', fn ($builder) => $builder->where('documento', 'like', '%'.$request->query('documento').'%'));
        }

        if ($request->filled('lote')) {
            $query->whereHas('lote', fn ($builder) => $builder->where('codigo', 'like', '%'.$request->query('lote').'%'));
        }

        if ($request->filled('manzano')) {
            $query->whereHas('lote.manzano', fn ($builder) => $builder->where('codigo', 'like', '%'.$request->query('manzano').'%'));
        }

        if ($request->filled('desde')) {
            $query->whereDate('fecha_reserva', '>=', $request->query('desde'));
        }

        if ($request->filled('hasta')) {
            $query->whereDate('fecha_reserva', '<=', $request->query('hasta'));
        }
    }

    public function create(Request $request, CommercialSettingsService $settings): View
    {
        $fechaReserva = now();
        $reserva = new Reserva([
            'fecha_reserva' => $fechaReserva,
            'fecha_vencimiento' => $request->user()->hasRole('vendedor')
                ? $settings->addBusinessDays($fechaReserva, $settings->reservaDiasHabilesAsesor())
                : $fechaReserva->copy()->addDays(7),
            'monto_reserva' => 0,
            'tipo_operacion' => 'contado',
        ]);

        if ($request->filled('lote_id')) {
            $reserva->lote_id = $request->integer('lote_id');
        }

        if ($request->filled('cliente_id')) {
            $cliente = Cliente::findOrFail($request->integer('cliente_id'));
            abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
            $reserva->cliente_id = $cliente->id;
        }

        return view('reservas.form', $this->formData($reserva));
    }

    public function store(StoreReservaRequest $request, ReservationService $reservationService): RedirectResponse
    {
        $lote = Lote::with('manzano')->findOrFail($request->validated('lote_id'));
        $cliente = Cliente::findOrFail($request->validated('cliente_id'));
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
        abort_if($request->user()->hasRole('vendedor') && $lote->estado !== 'disponible', 422, 'Solo puedes reservar lotes disponibles.');
        abort_if($request->user()->hasRole('vendedor') && ! UrbanizacionContext::userCanAccess($request->user(), (int) $lote->manzano->urbanizacion_id), 403, 'No tienes acceso a esta urbanizacion');

        $reservationService->create($request->validated(), $request->user());

        return redirect()->route('reservas.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function edit(Reserva $reserva): View
    {
        abort_unless(request()->user()?->can('editar reservas'), 403, 'No tienes permiso para editar esta reserva.');
        abort_unless(UrbanizacionContext::reservaBelongsToCurrent($reserva), 403, 'No tienes acceso a esta urbanizacion');

        return view('reservas.form', $this->formData($reserva));
    }

    public function update(StoreReservaRequest $request, Reserva $reserva, AuditService $auditService, LotService $lotService): RedirectResponse
    {
        abort_unless($request->user()->can('editar reservas'), 403, 'No tienes permiso para editar esta reserva.');
        abort_unless(UrbanizacionContext::reservaBelongsToCurrent($reserva), 403, 'No tienes acceso a esta urbanizacion');
        $cliente = Cliente::findOrFail($request->validated('cliente_id'));
        abort_unless(UrbanizacionContext::clienteBelongsToCurrent($cliente), 403, 'No tienes acceso a este cliente.');
        $lote = Lote::with('manzano')->findOrFail($request->validated('lote_id'));
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $before = $reserva->toArray();
        $previousLote = $reserva->lote;
        $reserva->update($request->safe()->except(['metodo_pago', 'referencia']));
        $lotService->syncStatusFromReservations($previousLote, 'reserva_actualizada', $request->user(), 'Reserva actualizada.');
        if ((int) $previousLote->id !== (int) $reserva->fresh()->lote_id) {
            $lotService->syncStatusFromReservations($reserva->fresh()->lote, 'reserva_actualizada', $request->user(), 'Reserva actualizada.');
        }
        $auditService->log($reserva, 'editar_reserva', 'Reserva actualizada.', $before, $reserva->fresh()->toArray(), $request);

        return redirect()->route('reservas.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function destroy(Request $request, Reserva $reserva, ReservationService $reservationService): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para cancelar reservas.');
        abort_unless(UrbanizacionContext::reservaBelongsToCurrent($reserva), 403, 'No tienes acceso a esta urbanizacion');

        $data = $request->validate(['motivo' => ['required', 'string', 'max:500']]);
        $reservationService->cancel($reserva, $request->user(), $data['motivo']);

        return back()->with('status', 'Operacion realizada correctamente.');
    }

    public function expire(Request $request, Reserva $reserva, ReservationService $reservationService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::reservaBelongsToCurrent($reserva), 403, 'No tienes acceso a esta urbanizacion');

        $reservationService->expire($reserva, $request->user());

        return back()->with('status', 'Operacion realizada correctamente.');
    }

    private function formData(Reserva $reserva): array
    {
        return [
            'reserva' => $reserva,
            'clientes' => UrbanizacionContext::clientes(Cliente::query())->orderBy('nombre')->get(),
            'lotes' => UrbanizacionContext::lotes(Lote::with('manzano.urbanizacion'))
                ->where(fn ($query) => $query->where('estado', 'disponible')->orWhere('id', $reserva->lote_id))
                ->orderBy('codigo')
                ->get(),
            'tiposOperacion' => Reserva::TIPOS_OPERACION,
        ];
    }
}
