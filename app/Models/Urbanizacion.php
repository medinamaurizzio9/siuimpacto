<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Urbanizacion extends Model
{
    protected $table = 'urbanizaciones';

    protected $fillable = [
        'nombre',
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
