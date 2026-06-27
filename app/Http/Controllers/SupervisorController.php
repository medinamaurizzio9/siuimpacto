<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\SupervisorProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DeletionDependencyService;
use App\Services\UserDeletionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    public function index(Request $request, UserDeletionService $deletionService): View
    {
        $query = SupervisorProfile::with('user')->latest();
        $this->applyIndexFilters($query, $request);

        $supervisores = $query->paginate(15)->appends($request->query());
        $supervisores->getCollection()->each(
            fn (SupervisorProfile $supervisor) => $supervisor->setAttribute('has_delete_history', $deletionService->hasHistoricalRecords($supervisor->user))
        );

        $asesorLideres = $this->asesorLeaderQuery($request)
            ->get()
            ->each(fn (Asesor $asesor) => $asesor->setAttribute(
                'has_delete_history',
                $asesor->user ? $deletionService->hasHistoricalRecords($asesor->user) : false
            ));

        return view('supervisores.index', [
            'supervisores' => $supervisores,
            'asesorLideres' => $asesorLideres,
        ]);
    }

    private function applyIndexFilters($query, Request $request): void
    {
        if ($request->filled('buscar')) {
            $term = trim((string) $request->query('buscar'));
            $query->where(function ($builder) use ($term): void {
                $builder->where('nombre', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            });
        }

        if (in_array($request->query('estado'), ['activo', 'inactivo'], true)) {
            $query->where('activo', $request->query('estado') === 'activo');
        }
    }

    private function asesorLeaderQuery(Request $request)
    {
        $supervisorUserIds = SupervisorProfile::query()
            ->whereNotNull('user_id')
            ->pluck('user_id');

        $query = Asesor::with('user')
            ->where('is_team_leader', true)
            ->whereNotNull('user_id')
            ->whereNotIn('user_id', $supervisorUserIds)
            ->latest();

        if ($request->filled('buscar')) {
            $term = trim((string) $request->query('buscar'));
            $query->where(function ($builder) use ($term): void {
                $builder->where('nombre', 'like', "%{$term}%")
                    ->orWhere('apellido', 'like', "%{$term}%")
                    ->orWhere('ci', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            });
        }

        if (in_array($request->query('estado'), ['activo', 'inactivo'], true)) {
            $query->where('activo', $request->query('estado') === 'activo');
        }

        return $query;
    }

    public function create(): View
    {
        return view('supervisores.form', ['supervisor' => new SupervisorProfile(['activo' => true])]);
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request);

        $supervisor = DB::transaction(function () use ($data, $request, $auditService) {
            $user = User::create([
                'name' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['ci']),
                'must_change_password' => true,
            ]);
            $user->assignRole('supervisor');

            $supervisor = SupervisorProfile::create([
                ...$data,
                'user_id' => $user->id,
                'activo' => $request->boolean('activo'),
            ]);
            $auditService->log($supervisor, 'crear_supervisor', 'Supervisor comercial creado con usuario de sistema.', null, $supervisor->toArray(), $request);

            return $supervisor;
        });

        return redirect()->route('supervisores.index')->with('status', 'Supervisor creado. La contrasena inicial es su CI y debera cambiarla al iniciar sesion.');
    }

    public function edit(SupervisorProfile $supervisor): View
    {
        return view('supervisores.form', ['supervisor' => $supervisor]);
    }

    public function update(Request $request, SupervisorProfile $supervisor, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request, $supervisor);
        $before = $supervisor->toArray();

        DB::transaction(function () use ($request, $supervisor, $data, $auditService, $before): void {
            $supervisor->update([...$data, 'activo' => $request->boolean('activo')]);
            $supervisor->user->update(['name' => $data['nombre'], 'email' => $data['email']]);
            $auditService->log($supervisor, 'editar_supervisor', 'Supervisor comercial actualizado.', $before, $supervisor->fresh()->toArray(), $request);
        });

        return redirect()->route('supervisores.index')->with('status', 'Supervisor actualizado.');
    }

    public function destroy(Request $request, SupervisorProfile $supervisor, AuditService $auditService, UserDeletionService $deletionService, DeletionDependencyService $dependencyService): RedirectResponse
    {
        $dependencies = $dependencyService->forSupervisor($supervisor);
        if ($dependencyService->hasDependencies($dependencies)) {
            return back()->withErrors([
                'delete' => $dependencyService->message('el supervisor', $dependencies),
            ]);
        }

        $result = $deletionService->deleteOrDeactivate($supervisor->user, $request, $auditService, 'eliminar_supervisor', 'desactivar_supervisor');

        return back()->with('status', $result === 'deleted'
            ? 'Usuario eliminado correctamente.'
            : 'El usuario tiene registros asociados, por seguridad fue desactivado.');
    }

    public function excel(): Response
    {
        return response()
            ->view('supervisores.export', ['supervisores' => SupervisorProfile::with('user')->get()])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="supervisores-impacto.xls"');
    }

    public function pdf(): Response
    {
        return Pdf::loadView('supervisores.export', ['supervisores' => SupervisorProfile::with('user')->get()])->download('supervisores-impacto.pdf');
    }

    private function validated(Request $request, ?SupervisorProfile $supervisor = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:50', Rule::unique('supervisor_profiles', 'ci')->ignore($supervisor)],
            'celular' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($supervisor?->user_id)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'activo' => ['nullable', 'boolean'],
        ]);
    }
}
