<?php

namespace App\Http\Controllers;

use App\Models\Asesor;
use App\Models\GrupoComercial;
use App\Models\SupervisorProfile;
use App\Models\Urbanizacion;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DeletionDependencyService;
use App\Services\UserDeletionService;
use App\Services\UserSpreadsheetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;
use Illuminate\View\View;

class AsesorController extends Controller
{
    public function index(Request $request, UserDeletionService $deletionService): View
    {
        $query = $this->scopedQuery($request);
        $this->applyIndexFilters($query, $request);

        $asesores = $query->paginate(15)->appends($request->query());
        $asesores->getCollection()->each(
            fn (Asesor $asesor) => $asesor->setAttribute('has_delete_history', $deletionService->hasHistoricalRecords($asesor->user))
        );

        return view('asesores.index', [
            'asesores' => $asesores,
            'grupos' => $this->availableGrupos($request),
            'supervisores' => $this->availableSupervisores($request),
            'urbanizaciones' => $this->availableUrbanizaciones($request),
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
                'is_team_leader' => $request->boolean('is_team_leader'),
            ]);

            $user->urbanizaciones()->sync($this->syncPayload($data['urbanizaciones'] ?? []));
            $this->syncTeamLeaderRole($asesor, $request->boolean('is_team_leader'));

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
                'is_team_leader' => $request->boolean('is_team_leader'),
            ]);
            $asesor->user->update([
                'name' => trim($data['nombre'].' '.$data['apellido']),
                'email' => $data['email'],
            ]);
            $asesor->user->urbanizaciones()->sync($this->syncPayload($data['urbanizaciones'] ?? []));
            $this->syncTeamLeaderRole($asesor, $request->boolean('is_team_leader'));

            $auditService->log($asesor, 'editar_asesor', 'Asesor actualizado.', $before, $asesor->fresh()->toArray(), $request);
            $auditService->log($asesor, 'asignar_urbanizaciones_asesor', 'Urbanizaciones actualizadas del asesor.', ['urbanizaciones' => $beforeUrbanizaciones], ['urbanizaciones' => $data['urbanizaciones'] ?? []], $request);
        });

        return redirect()->route('asesores.index')->with('status', 'Asesor actualizado.');
    }

    public function destroy(Request $request, Asesor $asesor, AuditService $auditService, UserDeletionService $deletionService, DeletionDependencyService $dependencyService): RedirectResponse
    {
        $dependencies = $dependencyService->forAsesor($asesor);
        if ($dependencyService->hasDependencies($dependencies)) {
            return back()->withErrors([
                'delete' => $dependencyService->message('el asesor', $dependencies),
            ]);
        }

        $result = $deletionService->deleteOrDeactivate($asesor->user, $request, $auditService, 'eliminar_asesor', 'desactivar_asesor');

        return back()->with('status', $result === 'deleted'
            ? 'Usuario eliminado correctamente.'
            : 'El usuario tiene registros asociados, por seguridad fue desactivado.');
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

    public function importForm(Request $request): View
    {
        $this->authorizeAdmin($request);

        return view('asesores.import');
    }

    public function import(Request $request, UserSpreadsheetService $spreadsheet, AuditService $auditService): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ]);

        try {
            $rows = $spreadsheet->rowsFromUpload($data['archivo']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['archivo' => $exception->getMessage()]);
        }

        $created = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($rows, &$created, &$skipped, &$errors): void {
            foreach ($rows as $index => $row) {
                $line = $index + 2;
                $nombre = trim((string) ($row['nombre'] ?? ''));
                $email = trim((string) ($row['email'] ?? ''));
                $password = (string) ($row['password'] ?? '');
                $rol = trim((string) ($row['rol'] ?? ''));
                $estado = trim((string) ($row['estado'] ?? '')) ?: 'activo';
                $telefono = trim((string) ($row['telefono'] ?? ''));
                $ci = trim((string) ($row['ci'] ?? ''));
                $urbanizacionNombre = trim((string) ($row['urbanizacion'] ?? ''));
                $supervisorNombre = trim((string) ($row['supervisor'] ?? ''));
                $urbanizacion = null;
                $supervisor = null;
                $rowErrors = [];

                Log::info('Import row', $row);

                if ($nombre === '') {
                    $rowErrors[] = "Fila {$line}: Nombre requerido.";
                }
                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = "Fila {$line}: Email invalido.";
                } elseif (User::where('email', $email)->exists()) {
                    $rowErrors[] = "Fila {$line}: El email ya existe.";
                }
                if (trim($password) === '') {
                    $rowErrors[] = "Fila {$line}: Contrasena requerida.";
                } elseif (mb_strlen($password) < 6) {
                    $rowErrors[] = "Fila {$line}: La contrasena debe tener al menos 6 caracteres.";
                }
                if (! in_array($rol, ['vendedor', 'supervisor'], true)) {
                    $rowErrors[] = "Fila {$line}: Rol no válido.";
                }
                if (! in_array($estado, ['activo', 'inactivo'], true)) {
                    $rowErrors[] = "Fila {$line}: Estado no valido.";
                }
                if ($urbanizacionNombre !== '') {
                    $urbanizacion = Urbanizacion::whereRaw('LOWER(nombre) = ?', [mb_strtolower($urbanizacionNombre)])->first();
                    if (! $urbanizacion) {
                        $rowErrors[] = "Fila {$line}: Urbanización no encontrada.";
                    }
                }
                if ($supervisorNombre !== '') {
                    $supervisor = User::role('supervisor')
                        ->where(fn ($query) => $query->where('name', $supervisorNombre)->orWhere('email', $supervisorNombre))
                        ->first();
                    if (! $supervisor) {
                        $rowErrors[] = "Fila {$line}: Supervisor no encontrado.";
                    }
                }

                if ($rowErrors !== []) {
                    $errors = array_merge($errors, $rowErrors);
                    $skipped++;
                    continue;
                }

                $user = User::create([
                    'name' => $nombre,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'must_change_password' => true,
                    'estado' => $estado,
                ]);
                $user->syncRoles([$rol]);

                if ($urbanizacion) {
                    $user->urbanizacionesAsignadas()->syncWithoutDetaching([$urbanizacion->id => ['activo' => $estado === 'activo']]);
                }

                $this->syncImportedProfile($user, $rol, [
                    'nombre' => $nombre,
                    'email' => $email,
                    'telefono' => $telefono,
                    'ci' => $ci,
                    'activo' => $estado === 'activo',
                    'supervisor_id' => $supervisor?->id,
                ]);
                $created++;
            }
        });

        $auditService->log(null, 'importar_usuarios', 'Importacion de equipo comercial.', null, [
            'creados' => $created,
            'omitidos' => $skipped,
            'errores' => count($errors),
        ], $request);

        return redirect()
            ->route('asesores.import')
            ->with('status', "Usuarios creados: {$created}. Usuarios omitidos: {$skipped}. Errores: ".count($errors).'.')
            ->with('import_errors', $errors);
    }

    public function template(Request $request, UserSpreadsheetService $spreadsheet): Response
    {
        $this->authorizeAdmin($request);

        $content = $spreadsheet->xlsx(UserSpreadsheetService::HEADERS, [
            ['Maria Lopez', 'maria@empresa.com', '12345678', 'supervisor', 'activo', '77722222', '2345678', 'Colinas del Norte Zona 1', ''],
            ['Juan Perez', 'juan@empresa.com', '12345678', 'vendedor', 'activo', '77711111', '1234567', 'Colinas del Norte Zona 1', 'Maria Lopez'],
        ]);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="plantilla_equipo_comercial.xlsx"',
        ]);
    }

    private function formData(Request $request, Asesor $asesor): array
    {
        $urbanizaciones = $request->user()->hasRole('supervisor')
            ? $request->user()->urbanizacionesAsignadas()->orderBy('nombre')->get()
            : Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get();

        return [
            'asesor' => $asesor,
            'supervisores' => $this->supervisorCandidates(),
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

    private function applyIndexFilters($query, Request $request): void
    {
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

        if ($request->filled('grupo_comercial_id')) {
            $query->where('grupo_comercial_id', $request->integer('grupo_comercial_id'));
        }

        if ($request->filled('supervisor_id') && ! $request->user()->hasRole('supervisor')) {
            $query->where('supervisor_id', $request->integer('supervisor_id'));
        }

        if ($request->filled('urbanizacion_id')) {
            $query->whereHas('user.urbanizacionesAsignadas', fn ($urbanizacionQuery) => $urbanizacionQuery
                ->where('urbanizaciones.id', $request->integer('urbanizacion_id')));
        }
    }

    private function availableUrbanizaciones(Request $request)
    {
        return $request->user()->hasRole('supervisor')
            ? $request->user()->urbanizacionesAsignadas()->orderBy('nombre')->get()
            : Urbanizacion::where('estado', 'activa')->orderBy('nombre')->get();
    }

    private function availableGrupos(Request $request)
    {
        return $request->user()->hasRole('supervisor')
            ? GrupoComercial::where('supervisor_id', $request->user()->id)->orderBy('nombre')->get()
            : GrupoComercial::orderBy('nombre')->get();
    }

    private function availableSupervisores(Request $request)
    {
        if ($request->user()->hasRole('supervisor')) {
            return User::whereKey($request->user()->id)->get();
        }

        return $this->supervisorCandidates();
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
            'is_team_leader' => ['nullable', 'boolean'],
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

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);
    }

    private function syncImportedProfile(User $user, string $rol, array $data): void
    {
        if ($rol === 'supervisor') {
            SupervisorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombre' => $data['nombre'],
                    'ci' => $data['ci'] !== '' ? $data['ci'] : null,
                    'celular' => $data['telefono'] ?: null,
                    'email' => $data['email'],
                    'activo' => $data['activo'],
                ]
            );
        }

        if ($rol === 'vendedor') {
            [$nombre, $apellido] = $this->splitName($data['nombre']);
            Asesor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'supervisor_id' => $data['supervisor_id'],
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'ci' => $data['ci'] !== '' ? $data['ci'] : null,
                    'celular' => $data['telefono'] ?: null,
                    'email' => $data['email'],
                    'activo' => $data['activo'],
                ]
            );
        }
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?: $name, $parts[1] ?? ''];
    }

    private function syncPayload(array $urbanizaciones): array
    {
        return collect($urbanizaciones)
            ->mapWithKeys(fn ($id) => [(int) $id => ['activo' => true]])
            ->all();
    }

    private function syncTeamLeaderRole(Asesor $asesor, bool $isTeamLeader): void
    {
        $asesor->loadMissing('user');
        $user = $asesor->user;

        if ($isTeamLeader) {
            $assignedBySystem = ! $user->hasRole('supervisor');

            if ($assignedBySystem) {
                $user->assignRole('supervisor');
            }

            $asesor->forceFill([
                'is_team_leader' => true,
                'team_leader_role_assigned' => $assignedBySystem || $asesor->team_leader_role_assigned,
            ])->save();

            return;
        }

        if ($asesor->team_leader_role_assigned && $user->hasRole('supervisor')) {
            $user->removeRole('supervisor');
        }

        $asesor->forceFill([
            'is_team_leader' => false,
            'team_leader_role_assigned' => false,
        ])->save();
    }

    private function supervisorCandidates()
    {
        $ids = User::role('supervisor')->pluck('id')
            ->merge(Asesor::where('is_team_leader', true)->pluck('user_id'))
            ->unique()
            ->values();

        return User::whereIn('id', $ids)->orderBy('name')->get();
    }
}
