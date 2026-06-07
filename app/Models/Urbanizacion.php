<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Urbanizacion extends Model
{
    protected $table = 'urbanizaciones';

    protected $fillable = [
        'nombre',
        'slug',
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

    protected static function booted(): void
    {
        static::saving(function (Urbanizacion $urbanizacion): void {
            if (! $urbanizacion->slug || $urbanizacion->isDirty('nombre')) {
                $urbanizacion->slug = static::uniqueSlug($urbanizacion->nombre, $urbanizacion->exists ? $urbanizacion->id : null);
            }
        });
    }

    private static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'urbanizacion';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
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
