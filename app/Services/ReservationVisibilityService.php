<?php

namespace App\Services;

use App\Models\Asesor;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReservationVisibilityService
{
    public function __construct(private CommercialAccessService $commercialAccess)
    {
    }

    public function visibleUserIds(User $user): ?array
    {
        return $this->commercialAccess->visibleUserIds($user);
    }

    public function apply(Builder $query, User $user): Builder
    {
        $ids = $this->visibleUserIds($user);

        return $ids === null ? $query : $query->whereIn('usuario_id', $ids);
    }

    public function vendedores(User $user): Collection
    {
        $query = User::whereHas('roles', fn ($builder) => $builder->whereIn('name', ['vendedor', 'supervisor', 'supervisor ventas', 'supervisor comercial']))->orderBy('name');
        $ids = $this->visibleUserIds($user);

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    public function canFilterUser(User $user, int $userId): bool
    {
        $ids = $this->visibleUserIds($user);

        return $ids === null || in_array($userId, $ids, true);
    }
}
