<?php

namespace App\Http\Controllers;

use App\Models\Urbanizacion;
use App\Models\UrbanizacionReferencia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionUrbanizacionGpsController extends Controller
{
    private array $allowedRoles = ['super administrador', 'administrador', 'gerente'];

    private array $tiposReferencia = ['ingreso', 'construccion', 'servicio', 'mirador', 'parque', 'transporte', 'sector', 'otro'];

    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $urbanizacionId = $request->integer('urbanizacion_id');
        $urbanizaciones = Urbanizacion::orderBy('nombre')->get();

        $referencias = UrbanizacionReferencia::with('urbanizacion')
            ->when($urbanizacionId, fn ($query) => $query->where('urbanizacion_id', $urbanizacionId))
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        return view('admin.urbanizacion-gps.index', compact('referencias', 'urbanizaciones', 'urbanizacionId'));
    }

    public function create(Request $request): View
    {
        $this->authorizeAccess($request);

        return view('admin.urbanizacion-gps.form', [
            'referencia' => new UrbanizacionReferencia([
                'urbanizacion_id' => $request->integer('urbanizacion_id') ?: session('urbanizacion_id'),
                'tipo_referencia' => 'otro',
                'activo' => true,
            ]),
            'urbanizaciones' => Urbanizacion::orderBy('nombre')->get(),
            'tiposReferencia' => $this->tiposReferencia,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($request);

        UrbanizacionReferencia::create($this->validated($request));

        return redirect()
            ->route('admin.urbanizacion-gps.index', ['urbanizacion_id' => $request->integer('urbanizacion_id')])
            ->with('status', 'Punto de referencia GPS registrado correctamente.');
    }

    public function edit(Request $request, UrbanizacionReferencia $urbanizacionReferencia): View
    {
        $this->authorizeAccess($request);

        return view('admin.urbanizacion-gps.form', [
            'referencia' => $urbanizacionReferencia,
            'urbanizaciones' => Urbanizacion::orderBy('nombre')->get(),
            'tiposReferencia' => $this->tiposReferencia,
        ]);
    }

    public function update(Request $request, UrbanizacionReferencia $urbanizacionReferencia): RedirectResponse
    {
        $this->authorizeAccess($request);

        $urbanizacionReferencia->update($this->validated($request));

        return redirect()
            ->route('admin.urbanizacion-gps.index', ['urbanizacion_id' => $urbanizacionReferencia->urbanizacion_id])
            ->with('status', 'Punto de referencia GPS actualizado correctamente.');
    }

    public function destroy(Request $request, UrbanizacionReferencia $urbanizacionReferencia): RedirectResponse
    {
        $this->authorizeAccess($request);

        $urbanizacionId = $urbanizacionReferencia->urbanizacion_id;
        $urbanizacionReferencia->delete();

        return redirect()
            ->route('admin.urbanizacion-gps.index', ['urbanizacion_id' => $urbanizacionId])
            ->with('status', 'Punto de referencia GPS eliminado correctamente.');
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole($this->allowedRoles), 403, 'No tienes permiso para configurar GPS de urbanizaciones.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'urbanizacion_id' => ['required', 'exists:urbanizaciones,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_referencia' => ['required', 'in:'.implode(',', $this->tiposReferencia)],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'plano_x' => ['nullable', 'required_with:plano_y', 'numeric', 'between:0,100'],
            'plano_y' => ['nullable', 'required_with:plano_x', 'numeric', 'between:0,100'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['activo'] = $request->boolean('activo');

        return $data;
    }
}
