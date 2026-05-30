<?php

namespace App\Http\Controllers;

use App\Models\SupervisorProfile;
use App\Models\User;
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
            'supervisores' => SupervisorProfile::with('user')->latest()->paginate(15),
        ]);
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
