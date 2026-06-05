<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\GrupoComercial;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Services\AuditService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AsesorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asesor::with('user', 'supervisor', 'grupo', 'user.urbanizacionesAsignadas')->latest();

        if ($request->user()->hasRole('supervisor')) {
            $query->where('supervisor_id', $request->user()->id);
        }

        return view('asesores.index', [
            'asesores' => $query->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        return view('asesores.form', $this->formData($request, new Asesor(['activo' => true])));
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request);

        $asesor = DB::transaction(function () use ($request, $data, $auditService): Asesor {
            $user = User::create([
                'name' => trim($data['nombre'].' '.$data['apellido']),
                'email' => $data['email'],
                'password' => Hash::make($data['ci']),
                'must_change_password' => true,
            ]);
            $user->assignRole('vendedor');

            $asesor = Asesor::create([
                'user_id' => $user->id,
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'grupo_comercial_id' => $data['grupo_comercial_id'] ?? null,
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'celular' => $data['celular'] ?? null,
                'email' => $data['email'],
                'direccion' => $data['direccion'] ?? null,
                'grupo_comercial' => $data['grupo_comercial'] ?? null,
                'activo' => $request->boolean('activo'),
            ]);

            $user->urbanizacionesAsignadas()->sync($this->syncPayload($data['urbanizaciones'] ?? []));
            if ($asesor->grupo_comercial_id) {
                $asesor->grupo->usuarios()->syncWithoutDetaching([$user->id => ['tipo' => 'vendedor', 'activo' => true]]);
            }

            $auditService->log($asesor, 'crear_asesor', 'Asesor creado con usuario de sistema.', null, $asesor->toArray(), $request);
            $auditService->log($asesor, 'asignar_urbanizaciones_asesor', 'Urbanizaciones asignadas al asesor.', null, ['urbanizaciones' => $data['urbanizaciones'] ?? []], $request);

            return $asesor;
        });

        return redirect()->route('asesores.index')->with('status', 'Asesor creado. La contrasena inicial es el CI del asesor. Debera cambiarla al iniciar sesion.');
    }

    public function edit(Request $request, Asesor $asesor): View
    {
        $this->authorizeManage($request, $asesor);

        return view('asesores.form', $this->formData($request, $asesor));
    }

    public function update(Request $request, Asesor $asesor, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManage($request, $asesor);
        $data = $this->validated($request, $asesor);
        $before = $asesor->toArray();
        $beforeUrbanizaciones = $asesor->user->urbanizacionesAsignadas()->pluck('urbanizaciones.id')->all();

        DB::transaction(function () use ($request, $asesor, $data, $auditService, $before, $beforeUrbanizaciones): void {
            $asesor->update([
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'grupo_comercial_id' => $data['grupo_comercial_id'] ?? null,
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'celular' => $data['celular'] ?? null,
                'email' => $data['email'],
                'direccion' => $data['direccion'] ?? null,
                'grupo_comercial' => $data['grupo_comercial'] ?? null,
                'activo' => $request->boolean('activo'),
            ]);
            $asesor->user->update([
                'name' => trim($data['nombre'].' '.$data['apellido']),
                'email' => $data['email'],
            ]);
            $asesor->user->urbanizacionesAsignadas()->sync($this->syncPayload($data['urbanizaciones'] ?? []));
            $asesor->user->gruposComerciales()->sync($asesor->grupo_comercial_id ? [$asesor->grupo_comercial_id => ['tipo' => 'vendedor', 'activo' => true]] : []);

            $auditService->log($asesor, 'editar_asesor', 'Asesor actualizado.', $before, $asesor->fresh()->toArray(), $request);
            $auditService->log($asesor, 'asignar_urbanizaciones_asesor', 'Urbanizaciones actualizadas del asesor.', ['urbanizaciones' => $beforeUrbanizaciones], ['urbanizaciones' => $data['urbanizaciones'] ?? []], $request);
        });

        return redirect()->route('asesores.index')->with('status', 'Asesor actualizado.');
    }

    public function destroy(Request $request, Asesor $asesor, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManage($request, $asesor, 'desactivar asesores');

        $before = $asesor->toArray();
        $asesor->update(['activo' => false]);
        $auditService->log($asesor, 'desactivar_asesor', 'Asesor desactivado.', $before, $asesor->fresh()->toArray(), $request);

        return back()->with('status', 'Asesor desactivado.');
    }

    public function resetPassword(Request $request, Asesor $asesor, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManage($request, $asesor, 'resetear contraseña asesor');

        $before = ['must_change_password' => $asesor->user->must_change_password];
        $asesor->user->update([
            'password' => Hash::make($asesor->ci),
            'must_change_password' => true,
        ]);
        $auditService->log($asesor, 'resetear_password_asesor', 'Contrasena del asesor reiniciada a su CI.', $before, ['must_change_password' => true], $request);

        return back()->with('status', 'Contrasena reiniciada. El asesor debera cambiarla al iniciar sesion.');
    }

    public function excel(Request $request): Response
    {
        return response()
            ->view('asesores.export', ['asesores' => $this->scopedQuery($request)->get()])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="asesores-impacto.xls"');
    }

    public function pdf(Request $request): Response
    {
        return Pdf::loadView('asesores.export', ['asesores' => $this->scopedQuery($request)->get()])->download('asesores-impacto.pdf');
    }

    private function formData(Request $request, Asesor $asesor): array
    {
        $urbanizaciones = $request->user()->hasRole('supervisor')
            ? $request->user()->urbanizacionesAsignadas()->orderBy('nombre')->get()
            : Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get();

        return [
            'asesor' => $asesor,
            'supervisores' => User::role('supervisor')->orderBy('name')->get(),
            'grupos' => $request->user()->hasRole('supervisor')
                ? GrupoComercial::where('supervisor_id', $request->user()->id)->where('activo', true)->orderBy('nombre')->get()
                : GrupoComercial::where('activo', true)->orderBy('nombre')->get(),
            'urbanizaciones' => $urbanizaciones,
            'urbanizacionesAsignadas' => $asesor->exists ? $asesor->user->urbanizacionesAsignadas()->pluck('urbanizaciones.id')->all() : [],
        ];
    }

    private function scopedQuery(Request $request)
    {
        $query = Asesor::with('user', 'supervisor', 'grupo', 'user.urbanizacionesAsignadas')->latest();

        if ($request->user()->hasRole('supervisor')) {
            $query->where('supervisor_id', $request->user()->id);
        }

        return $query;
    }

    private function validated(Request $request, ?Asesor $asesor = null): array
    {
        abort_unless($request->user()->can($asesor?->exists ? 'editar asesores' : 'crear asesores'), 403);

        $allowedUrbanizaciones = $request->user()->hasRole('supervisor')
            ? $request->user()->urbanizacionesAsignadas()->pluck('urbanizaciones.id')->all()
            : Urbanizacion::where('estado', 'activa')->pluck('id')->all();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:50', Rule::unique('asesores', 'ci')->ignore($asesor)],
            'celular' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($asesor?->user_id)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'grupo_comercial_id' => ['nullable', 'exists:grupos_comerciales,id'],
            'grupo_comercial' => ['nullable', 'string', 'max:255'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'urbanizaciones' => ['nullable', 'array'],
            'urbanizaciones.*' => ['integer', Rule::in($allowedUrbanizaciones)],
            'activo' => ['nullable', 'boolean'],
        ]);

        if ($request->user()->hasRole('supervisor')) {
            $data['supervisor_id'] = $request->user()->id;
            if (! empty($data['grupo_comercial_id'])) {
                abort_unless(GrupoComercial::whereKey($data['grupo_comercial_id'])->where('supervisor_id', $request->user()->id)->exists(), 403);
            }
        }

        return $data;
    }

    private function authorizeManage(Request $request, Asesor $asesor, string $permission = 'editar asesores'): void
    {
        abort_unless($request->user()->can($permission), 403);

        if ($request->user()->hasRole('supervisor')) {
            abort_unless((int) $asesor->supervisor_id === $request->user()->id, 403, 'Solo puedes gestionar asesores de tu equipo.');
        }
    }

    private function syncPayload(array $urbanizaciones): array
    {
        return collect($urbanizaciones)
            ->mapWithKeys(fn ($id) => [(int) $id => ['activo' => true]])
            ->all();
    }
}
