<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordChangeController extends Controller
{
    public function edit(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request, AuditService $auditService): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $before = ['must_change_password' => $user->must_change_password];

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        $auditService->log($user, 'asesor_cambia_password', 'Asesor cambio su contrasena obligatoria.', $before, ['must_change_password' => false], $request);

        return redirect()->route('urbanizaciones.select')->with('status', 'Contrasena actualizada correctamente.');
    }
}
