<?php

namespace App\Http\Controllers;

use App\Models\SupervisorProfile;
use App\Models\User;
use App\Models\GrupoComercial;
use App\Services\AuditService;
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
    public function index(): View
    {
        return view('supervisores.index', [
            'supervisores' => SupervisorProfile::with('user', 'supervisorComercial', 'grupo')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('supervisores.form', $this->formData(new SupervisorProfile(['activo' => true, 'tipo' => 'supervisor_comercial'])));
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
            $user->assignRole([$data['tipo'] === 'supervisor_ventas' ? 'supervisor ventas' : 'supervisor comercial', 'supervisor']);

            $supervisor = SupervisorProfile::create([
                ...$data,
                'user_id' => $user->id,
                'activo' => $request->boolean('activo'),
            ]);
            if (! empty($data['grupo_comercial_id'])) {
                $user->gruposComerciales()->syncWithoutDetaching([$data['grupo_comercial_id'] => ['tipo' => $data['tipo'], 'activo' => true]]);
            }
            $auditService->log($supervisor, 'crear_supervisor', 'Supervisor comercial creado con usuario de sistema.', null, $supervisor->toArray(), $request);

            return $supervisor;
        });

        return redirect()->route('supervisores.index')->with('status', 'Supervisor creado. La contrasena inicial es su CI y debera cambiarla al iniciar sesion.');
    }

    public function edit(SupervisorProfile $supervisor): View
    {
        return view('supervisores.form', $this->formData($supervisor));
    }

    public function update(Request $request, SupervisorProfile $supervisor, AuditService $auditService): RedirectResponse
    {
        $data = $this->validated($request, $supervisor);
        $before = $supervisor->toArray();

        DB::transaction(function () use ($request, $supervisor, $data, $auditService, $before): void {
            $supervisor->update([...$data, 'activo' => $request->boolean('activo')]);
            $supervisor->user->update(['name' => $data['nombre'], 'email' => $data['email']]);
            $supervisor->user->syncRoles([$data['tipo'] === 'supervisor_ventas' ? 'supervisor ventas' : 'supervisor comercial', 'supervisor']);
            if (! empty($data['grupo_comercial_id'])) {
                $supervisor->user->gruposComerciales()->sync([$data['grupo_comercial_id'] => ['tipo' => $data['tipo'], 'activo' => true]]);
            }
            $auditService->log($supervisor, 'editar_supervisor', 'Supervisor comercial actualizado.', $before, $supervisor->fresh()->toArray(), $request);
        });

        return redirect()->route('supervisores.index')->with('status', 'Supervisor actualizado.');
    }

    public function destroy(Request $request, SupervisorProfile $supervisor, AuditService $auditService): RedirectResponse
    {
        $before = $supervisor->toArray();
        $supervisor->update(['activo' => false]);
        $auditService->log($supervisor, 'desactivar_supervisor', 'Supervisor comercial desactivado.', $before, $supervisor->fresh()->toArray(), $request);

        return back()->with('status', 'Supervisor desactivado.');
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
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:50', Rule::unique('supervisor_profiles', 'ci')->ignore($supervisor)],
            'celular' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($supervisor?->user_id)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'tipo' => ['nullable', 'in:supervisor_comercial,supervisor_ventas'],
            'supervisor_comercial_id' => ['nullable', 'exists:users,id'],
            'grupo_comercial_id' => ['nullable', 'exists:grupos_comerciales,id'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $data['tipo'] ??= $supervisor?->tipo ?? 'supervisor_comercial';

        return $data;
    }

    private function formData(SupervisorProfile $supervisor): array
    {
        return [
            'supervisor' => $supervisor,
            'supervisoresComerciales' => User::role('supervisor comercial')->orderBy('name')->get(),
            'grupos' => GrupoComercial::where('activo', true)->orderBy('nombre')->get(),
        ];
    }
}
