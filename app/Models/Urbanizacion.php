<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Urbanizacion extends Model
{
    protected $table = 'urbanizaciones';

    protected $fillable = [
        'nombre',
        'propietario',
        'ubicacion',
        'descripcion',
        'plano_imagen',
        'superficie_total',
        'estado',
        'mostrar_precio_publico',
    ];

    protected function casts(): array
    {
        return [
            'mostrar_precio_publico' => 'boolean',
        ];
    }

    public function manzanos(): HasMany
    {
        return $this->hasMany(Manzano::class);
    }

    public function scopeWithLotStats(Builder $query): Builder
    {
        return $query->withCount([
            'manzanos',
            'lotes as total_lotes',
            'lotes as disponibles_count' => fn (Builder $query) => $query->where('estado', 'disponible'),
            'lotes as vendidos_count' => fn (Builder $query) => $query->where('estado', 'vendido'),
            'lotes as reservados_count' => fn (Builder $query) => $query->where('estado', 'reservado'),
            'lotes as bloqueados_count' => fn (Builder $query) => $query->where('estado', 'bloqueado'),
        ]);
    }

    public function lotes(): HasManyThrough
    {
        return $this->hasManyThrough(Lote::class, Manzano::class);
    }

    public function asesores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'urbanizacion_user')
            ->withPivot('activo')
            ->withTimestamps();
    }
}
