<?php

namespace App\Http\Controllers;

use App\Models\Manzano;
use App\Models\Urbanizacion;
use App\Support\UrbanizacionContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManzanoController extends Controller
{
    public function index(): View
    {
        return view('manzanos.index', [
            'manzanos' => Manzano::with('urbanizacion')->withCount('lotes')->where('urbanizacion_id', UrbanizacionContext::currentId())->orderBy('codigo')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('manzanos.form', $this->formData(new Manzano()));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless((int) $request->input('urbanizacion_id') === UrbanizacionContext::currentId(), 403, 'No tienes acceso a esta urbanizacion');

        Manzano::create($this->validated($request));

        return redirect()->route('manzanos.index')->with('status', 'Manzano creado.');
    }

    public function edit(Manzano $manzano): View
    {
        abort_unless((int) $manzano->urbanizacion_id === UrbanizacionContext::currentId(), 403, 'No tienes acceso a esta urbanizacion');

        return view('manzanos.form', $this->formData($manzano));
    }

    public function update(Request $request, Manzano $manzano): RedirectResponse
    {
        abort_unless((int) $manzano->urbanizacion_id === UrbanizacionContext::currentId(), 403, 'No tienes acceso a esta urbanizacion');
        abort_unless((int) $request->input('urbanizacion_id') === UrbanizacionContext::currentId(), 403, 'No tienes acceso a esta urbanizacion');

        $manzano->update($this->validated($request));

        return redirect()->route('manzanos.index')->with('status', 'Manzano actualizado.');
    }

    public function destroy(Manzano $manzano): RedirectResponse
    {
        abort_unless(request()->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para eliminar manzanos.');
        abort_unless((int) $manzano->urbanizacion_id === UrbanizacionContext::currentId(), 403, 'No tienes acceso a esta urbanizacion');

        $manzano->delete();

        return back()->with('status', 'Manzano eliminado.');
    }

    private function formData(Manzano $manzano): array
    {
        return ['manzano' => $manzano, 'urbanizaciones' => Urbanizacion::whereKey(UrbanizacionContext::currentId())->get()];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'urbanizacion_id' => ['required', 'exists:urbanizaciones,id'],
            'codigo' => ['required', 'string', 'max:50'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
