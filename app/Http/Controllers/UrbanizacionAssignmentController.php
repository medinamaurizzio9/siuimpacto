<?php

namespace App\Http\Controllers;

use App\Models\Urbanizacion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UrbanizacionAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasAnyRole(['super administrador', 'administrador', 'gerente']), 403, 'No tienes permiso para ver asignaciones.');

        $usuariosQuery = User::role('vendedor')
            ->with('urbanizacionesAsignadas', 'asesor')
            ->orderBy('name');

        if ($request->filled('buscar')) {
            $term = trim((string) $request->query('buscar'));
            $usuariosQuery->where(function ($query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('asesor', fn ($asesorQuery) => $asesorQuery
                        ->where('ci', 'like', "%{$term}%")
                        ->orWhere('nombre', 'like', "%{$term}%")
                        ->orWhere('apellido', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('urbanizacion_id')) {
            $usuariosQuery->whereHas('urbanizacionesAsignadas', fn ($query) => $query
                ->where('urbanizaciones.id', $request->integer('urbanizacion_id')));
        }

        if (in_array($request->query('estado'), ['activo', 'inactivo'], true)) {
            $usuariosQuery->where('estado', $request->query('estado'));
        }

        if ($request->boolean('solo_activos')) {
            $usuariosQuery->where('estado', 'activo');
        }

        return view('urbanizaciones.asignaciones', [
            'usuarios' => $usuariosQuery->paginate(15)->appends($request->query()),
            'urbanizaciones' => Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get(),
            'soloLectura' => ! $request->user()->hasAnyRole(['super administrador', 'administrador']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['super administrador', 'administrador']), 403, 'Solo el administrador puede asignar urbanizaciones.');
        abort_unless($user->hasRole('vendedor'), 422, 'Solo se pueden asignar urbanizaciones a vendedores.');

        $data = $request->validate([
            'urbanizaciones' => ['nullable', 'array'],
            'urbanizaciones.*' => ['integer', 'exists:urbanizaciones,id'],
        ]);

        $sync = collect($data['urbanizaciones'] ?? [])
            ->mapWithKeys(fn ($id) => [(int) $id => ['activo' => true]])
            ->all();

        $user->urbanizacionesAsignadas()->sync($sync);

        return back()->with('status', 'Asignaciones actualizadas.');
    }
}
