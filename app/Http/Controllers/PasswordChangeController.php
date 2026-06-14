<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->must_change_password) {
            return $this->redirectAfterPasswordChange($request);
        }

        return view('auth.change-password');
    }

    public function update(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contrasena actual no es correcta.']);
        }

        if (Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'La nueva contrasena debe ser diferente a la actual.']);
        }

        $before = ['must_change_password' => $user->must_change_password];

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        $auditService->log($user, 'cambio_password_obligatorio', 'Cambio obligatorio de contrasena.', $before, ['must_change_password' => false], $request);

        return $this->redirectAfterPasswordChange($request)->with('status', 'Contrasena actualizada correctamente.');
    }

    private function redirectAfterPasswordChange(Request $request): RedirectResponse
    {
        if ($request->user()?->hasRole('cliente')) {
            return redirect()->route('clientes.mi-cuenta');
        }

        return redirect()->intended(route('urbanizaciones.select'));
    }
}
