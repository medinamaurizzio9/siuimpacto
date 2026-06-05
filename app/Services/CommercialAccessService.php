<?php

namespace App\Services;

use App\Models\Asesor;
use App\Models\GrupoComercial;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CommercialAccessService
{
    public function isGlobal(User $user): bool
    {
        return $user->hasAnyRole(['super administrador', 'administrador', 'gerente']);
    }

    public function accessibleUrbanizacionIds(User $user): array
    {
        if ($this->isGlobal($user)) {
            return \App\Models\Urbanizacion::where('estado', 'activa')->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $direct = $user->urbanizacionesAsignadas()->pluck('urbanizaciones.id');
        $groups = $user->gruposComerciales()
            ->wherePivot('activo', true)
            ->with('urbanizaciones')
            ->get()
            ->flatMap(fn (GrupoComercial $grupo) => $grupo->urbanizaciones->pluck('id'));

        return $direct->merge($groups)->unique()->map(fn ($id) => (int) $id)->values()->all();
    }

    public function canAccessUrbanizacion(User $user, int $urbanizacionId): bool
    {
        if ($user->roles()->doesntExist()) {
            return true;
        }

        return in_array($urbanizacionId, $this->accessibleUrbanizacionIds($user), true);
    }

    public function ensureCanAccessLote(User $user, Lote $lote): void
    {
        abort_unless($this->canAccessUrbanizacion($user, (int) $lote->manzano->urbanizacion_id), 403, 'No tienes acceso a esta urbanizacion');
    }

    public function visibleUserIds(User $user): ?array
    {
        if ($this->isGlobal($user)) {
            return null;
        }

        if ($user->hasRole('supervisor comercial')) {
            $groupIds = $user->gruposResponsables()->pluck('id');

            return User::whereHas('gruposComerciales', fn (Builder $query) => $query->whereIn('grupos_comerciales.id', $groupIds))
                ->pluck('id')->push($user->id)->unique()->values()->all();
        }

        if ($user->hasAnyRole(['supervisor ventas', 'supervisor'])) {
            return Asesor::where('supervisor_id', $user->id)->pluck('user_id')->push($user->id)->unique()->values()->all();
        }

        return [$user->id];
    }

    public function applyVentas(Builder $query, User $user): Builder
    {
        $query->whereIn('urbanizacion_id', $this->accessibleUrbanizacionIds($user));
        if ($user->hasRole('supervisor comercial')) {
            return $query;
        }
        $ids = $this->visibleUserIds($user);

        return $ids === null ? $query : $query->where(function (Builder $builder) use ($ids): void {
            $builder->whereIn('vendedor_id', $ids)
                ->orWhereIn('supervisor_ventas_id', $ids)
                ->orWhereIn('supervisor_comercial_id', $ids)
                ->orWhereIn('usuario_creador_id', $ids);
        });
    }

    public function applyReservas(Builder $query, User $user): Builder
    {
        $query->whereIn('urbanizacion_id', $this->accessibleUrbanizacionIds($user));
        if ($user->hasRole('supervisor comercial')) {
            return $query;
        }
        $ids = $this->visibleUserIds($user);

        return $ids === null ? $query : $query->where(function (Builder $builder) use ($ids): void {
            $builder->whereIn('vendedor_id', $ids)
                ->orWhereIn('supervisor_ventas_id', $ids)
                ->orWhereIn('supervisor_comercial_id', $ids)
                ->orWhereIn('usuario_id', $ids);
        });
    }

    public function hierarchyFor(User $user, array $overrides = []): array
    {
        if ($user->hasRole('super administrador')) {
            return [
                'vendedor_id' => $overrides['vendedor_id'] ?? null,
                'supervisor_ventas_id' => $overrides['supervisor_ventas_id'] ?? null,
                'supervisor_comercial_id' => $overrides['supervisor_comercial_id'] ?? null,
                'grupo_comercial_id' => $overrides['grupo_comercial_id'] ?? null,
            ];
        }

        $asesor = $user->asesor()->with('grupo')->first();
        $profile = $user->supervisorProfile()->first();
        $grupo = $asesor?->grupo
            ?? ($profile?->grupo_comercial_id ? GrupoComercial::find($profile->grupo_comercial_id) : null)
            ?? $user->gruposResponsables()->first();

        return [
            'vendedor_id' => $user->hasRole('vendedor') ? $user->id : null,
            'supervisor_ventas_id' => $user->hasAnyRole(['supervisor', 'supervisor ventas']) ? $user->id : $asesor?->supervisor_id,
            'supervisor_comercial_id' => $user->hasRole('supervisor comercial')
                ? $user->id
                : ($profile?->supervisor_comercial_id ?? $grupo?->supervisor_id),
            'grupo_comercial_id' => $grupo?->id,
        ];
    }

    public function gruposVisibles(User $user): Collection
    {
        if ($this->isGlobal($user)) {
            return GrupoComercial::orderBy('nombre')->get();
        }

        return $user->gruposComerciales()->wherePivot('activo', true)->orderBy('nombre')->get()
            ->merge($user->gruposResponsables()->orderBy('nombre')->get())
            ->unique('id')->values();
    }
}
