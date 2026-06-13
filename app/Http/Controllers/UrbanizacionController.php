<?php

namespace App\Http\Controllers;

use App\Models\Urbanizacion;
use App\Services\PlanImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UrbanizacionController extends Controller
{
    public function index(): View
    {
        return view('urbanizaciones.index', [
            'urbanizaciones' => Urbanizacion::withLotStats()->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('urbanizaciones.form', ['urbanizacion' => new Urbanizacion()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Urbanizacion::create($this->validated($request));

        return redirect()->route('urbanizaciones.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function edit(Urbanizacion $urbanizacion): View
    {
        return view('urbanizaciones.form', compact('urbanizacion'));
    }

    public function update(Request $request, Urbanizacion $urbanizacion): RedirectResponse
    {
        $urbanizacion->update($this->validated($request, $urbanizacion));

        return redirect()->route('urbanizaciones.index')->with('status', 'Operacion realizada correctamente.');
    }

    public function destroy(Urbanizacion $urbanizacion): RedirectResponse
    {
        abort_unless(request()->user()->hasAnyRole(['administrador', 'gerente']), 403, 'No tienes permiso para eliminar urbanizaciones.');

        $urbanizacion->delete();

        return back()->with('status', 'Urbanizacion eliminada.');
    }

    private function validated(Request $request, ?Urbanizacion $urbanizacion = null): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'propietario' => ['nullable', 'string', 'max:255'],
            'ubicacion' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'plano_imagen' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:51200'],
            'superficie_total' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'in:activa,pausada,cerrada'],
            'mostrar_precio_publico' => ['nullable', 'boolean'],
        ]);

        $data['mostrar_precio_publico'] = $request->boolean('mostrar_precio_publico');

        if ($request->hasFile('plano_imagen')) {
            $planImageService = app(PlanImageService::class);

            try {
                $storedPlan = $planImageService->store($request->file('plano_imagen'));
            } catch (\RuntimeException) {
                throw ValidationException::withMessages([
                    'plano_imagen' => 'No se pudo convertir el PDF. Suba una imagen JPG o PNG en alta resolución.',
                ]);
            }

            if ($urbanizacion?->plano_imagen || $urbanizacion?->plano_archivo_original) {
                $planImageService->delete($urbanizacion->plano_imagen, $urbanizacion->plano_archivo_original);
            }

            $data['plano_imagen'] = $storedPlan['plano_imagen'];
            $data['plano_archivo_original'] = $storedPlan['plano_archivo_original'];
        } else {
            unset($data['plano_imagen']);
            unset($data['plano_archivo_original']);
        }

        $data['slug'] = $this->uniqueSlug($data['nombre'], $urbanizacion);

        return $data;
    }

    private function uniqueSlug(string $name, ?Urbanizacion $urbanizacion = null): string
    {
        $base = Str::slug($name) ?: 'urbanizacion';
        $slug = $base;
        $suffix = 2;

        while (Urbanizacion::where('slug', $slug)
            ->when($urbanizacion, fn ($query) => $query->whereKeyNot($urbanizacion->id))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
