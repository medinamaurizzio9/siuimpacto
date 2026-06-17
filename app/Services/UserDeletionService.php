<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserDeletionService
{
    public function deleteOrDeactivate(User $user, Request $request, AuditService $auditService, string $deleteAction, string $deactivateAction): string
    {
        abort_unless($request->user()?->hasRole('administrador'), 403);

        if ($this->hasHistoricalRecords($user)) {
            $before = $this->snapshot($user);
            $this->deactivate($user);
            $auditService->log($user, $deactivateAction, 'Usuario desactivado para conservar historial.', $before, $this->snapshot($user->fresh()), $request);

            return 'deactivated';
        }

        $before = $this->snapshot($user);
        $auditService->log($user, $deleteAction, 'Usuario eliminado sin historial asociado.', $before, null, $request);
        $user->delete();

        return 'deleted';
    }

    public function hasHistoricalRecords(User $user): bool
    {
        $id = $user->id;

        return DB::table('ventas')->where('user_id', $id)->exists()
            || DB::table('reservas')->where('usuario_id', $id)->exists()
            || DB::table('clientes')->where('created_by', $id)->exists()
            || DB::table('cash_movements')->where('user_id', $id)->exists()
            || DB::table('audit_logs')->where('user_id', $id)->exists()
            || DB::table('lot_histories')->where('user_id', $id)->exists()
            || DB::table('asesores')->where('supervisor_id', $id)->exists()
            || DB::table('grupos_comerciales')->where('supervisor_id', $id)->exists();
    }

    private function deactivate(User $user): void
    {
        $user->update(['estado' => 'inactivo']);
        $user->asesor?->update(['activo' => false]);
        $user->supervisorProfile?->update(['activo' => false]);
        $user->urbanizacionesAsignadas()->newPivotStatement()
            ->where('user_id', $user->id)
            ->update(['activo' => false, 'updated_at' => now()]);
    }

    private function snapshot(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'estado' => $user->estado,
            'asesor_activo' => $user->asesor?->activo,
            'supervisor_activo' => $user->supervisorProfile?->activo,
        ];
    }
}
