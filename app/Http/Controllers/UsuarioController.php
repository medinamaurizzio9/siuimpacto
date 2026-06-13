<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    private array $allowedRoles = [
        'administrador',
        'gerente',
        'supervisor',
        'vendedor',
        'cliente',
    ];

    public function index(Request $request): View
    {
        $this->authorizeManageUsers($request);
        $sort = $this->sort($request);

        $users = User::with('roles')
            ->tap(fn ($query) => $this->applySorting($query, $sort['field'], $sort['direction']))
            ->paginate(50)
            ->appends($request->query());

        return view('administracion.usuarios.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $this->authorizeManageUsers($request);

        return view('administracion.usuarios.create', [
            'usuario' => new User(),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManageUsers($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in($this->roles()->pluck('name')->all())],
        ]);

        $user = DB::transaction(function () use ($data, $request, $auditService): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'must_change_password' => false,
            ]);
            $user->syncRoles([$data['rol']]);

            $auditService->log($user, 'crear_usuario', 'Creacion de usuario del sistema.', null, [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $data['rol'],
            ], $request);

            return $user;
        });

        return redirect()
            ->route('admin.usuarios')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function edit(Request $request, User $usuario): View
    {
        $this->authorizeManageUsers($request);

        $usuario->load('roles');

        return view('administracion.usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $usuario, AuditService $auditService): RedirectResponse
    {
        $this->authorizeManageUsers($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', Rule::in($this->roles()->pluck('name')->all())],
        ]);

        DB::transaction(function () use ($usuario, $data, $request, $auditService): void {
            $usuario->load('roles');
            $before = [
                'name' => $usuario->name,
                'email' => $usuario->email,
                'roles' => $usuario->roles->pluck('name')->values()->all(),
            ];

            $updates = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (! empty($data['password'])) {
                $updates['password'] = $data['password'];
            }

            $usuario->update($updates);
            $usuario->syncRoles([$data['rol']]);

            $auditService->log($usuario, 'editar_usuario', 'Edicion de usuario del sistema.', $before, [
                'name' => $usuario->name,
                'email' => $usuario->email,
                'roles' => [$data['rol']],
                'password_changed' => ! empty($data['password']),
            ], $request);
        });

        return redirect()
            ->route('admin.usuarios')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    private function roles()
    {
        return Role::whereIn('name', $this->allowedRoles)
            ->orderBy('name')
            ->get();
    }

    private function authorizeManageUsers(Request $request): void
    {
        abort_unless($request->user()?->hasAnyRole(['administrador', 'super administrador']), 403);
    }

    private function sort(Request $request): array
    {
        $allowedSorts = ['id', 'name', 'email', 'rol'];
        $field = $request->query('sort', 'id');

        return [
            'field' => in_array($field, $allowedSorts, true) ? $field : 'id',
            'direction' => $request->query('direction') === 'desc' ? 'desc' : 'asc',
        ];
    }

    private function applySorting($query, string $field, string $direction): void
    {
        if ($field !== 'rol') {
            $query->orderBy($field, $direction);

            return;
        }

        $query->orderBy(
            Role::select('name')
                ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->limit(1),
            $direction
        );
    }
}
