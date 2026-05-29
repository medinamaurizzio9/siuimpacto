<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoteRequest;
use App\Models\Lote;
use App\Models\Manzano;
use App\Services\AuditService;
use App\Services\LotService;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoteController extends Controller
{
    public function index(): View
    {
        return view('lotes.index', [
            'lotes' => UrbanizacionContext::lotes(Lote::with('manzano.urbanizacion'))->orderBy('estado')->orderBy('codigo')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('lotes.form', $this->formData(new Lote(['estado' => 'disponible', 'fila' => 1, 'columna' => 1, 'coord_x' => null, 'coord_y' => null])));
    }

    public function store(StoreLoteRequest $request, AuditService $auditService): RedirectResponse
    {
        abort_unless(Manzano::whereKey($request->validated('manzano_id'))->where('urbanizacion_id', UrbanizacionContext::currentId())->exists(), 403, 'No tienes acceso a esta urbanizacion');

        $lote = Lote::create($request->validated());
        $auditService->log($lote, 'crear_lote', 'Lote creado.', null, $lote->toArray(), $request);

        return redirect()->route('lotes.index')->with('status', 'Lote creado.');
    }

    public function show(Lote $lote): View
    {
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $lote->load('manzano.urbanizacion', 'reservaActiva.cliente', 'venta.cliente');

        return view('lotes.show', ['lote' => $lote]);
    }

    public function edit(Lote $lote): View
    {
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        return view('lotes.form', $this->formData($lote));
    }

    public function update(StoreLoteRequest $request, Lote $lote, LotService $lotService, AuditService $auditService): RedirectResponse
    {
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless(Manzano::whereKey($request->validated('manzano_id'))->where('urbanizacion_id', UrbanizacionContext::currentId())->exists(), 403, 'No tienes acceso a esta urbanizacion');

        if ($lote->estado !== $request->validated('estado')) {
            $request->validate(['motivo_cambio_estado' => ['required', 'string', 'max:500']]);
        }

        $before = $lote->only(['precio', 'estado']);
        $auditBefore = $lote->toArray();
        $lote->update($request->validated());
        $lotService->trackManualChanges($lote, $before, $request->user());
        $auditService->log($lote, 'editar_lote', 'Lote actualizado.', $auditBefore, $lote->fresh()->toArray(), $request);

        return redirect()->route('lotes.index')->with('status', 'Lote actualizado.');
    }

    public function destroy(\Illuminate\Http\Request $request, Lote $lote, AuditService $auditService): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para eliminar lotes.');
        abort_unless(UrbanizacionContext::loteBelongsToCurrent($lote), 403, 'No tienes acceso a esta urbanizacion');

        $before = $lote->toArray();
        $auditService->log($lote, 'eliminar_lote', 'Lote eliminado.', $before, null, $request);
        $lote->delete();

        return back()->with('status', 'Lote eliminado.');
    }

    private function formData(Lote $lote): array
    {
        return [
            'lote' => $lote,
            'manzanos' => Manzano::with('urbanizacion')->where('urbanizacion_id', UrbanizacionContext::currentId())->orderBy('codigo')->get(),
            'estados' => Lote::ESTADOS,
        ];
    }
}
