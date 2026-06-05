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

        return view('urbanizaciones.asignaciones', [
            'usuarios' => User::role(['vendedor', 'supervisor comercial', 'supervisor ventas', 'supervisor'])->with('urbanizacionesAsignadas', 'roles')->orderBy('name')->get(),
            'urbanizaciones' => Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get(),
            'soloLectura' => ! $request->user()->hasRole('super administrador'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->hasRole('super administrador'), 403, 'Solo el Super Administrador puede asignar urbanizaciones.');
        abort_unless($user->hasAnyRole(['vendedor', 'supervisor comercial', 'supervisor ventas', 'supervisor']), 422, 'Solo se pueden asignar urbanizaciones al equipo comercial.');

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
