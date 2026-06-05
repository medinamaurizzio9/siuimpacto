<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoComercial extends Model
{
    protected $table = 'grupos_comerciales';

    protected $fillable = [
        'nombre',
        'descripcion',
        'observaciones',
        'supervisor_id',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function asesores(): HasMany
    {
        return $this->hasMany(Asesor::class);
    }

    public function urbanizaciones(): BelongsToMany
    {
        return $this->belongsToMany(Urbanizacion::class, 'grupo_comercial_urbanizacion')
            ->withPivot('activo')
            ->withTimestamps()
            ->wherePivot('activo', true);
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'grupo_comercial_user')
            ->withPivot(['tipo', 'activo'])
            ->withTimestamps();
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }
}
