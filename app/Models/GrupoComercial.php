<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoComercial extends Model
{
    protected $table = 'grupos_comerciales';

    protected $fillable = [
        'nombre',
        'descripcion',
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
}
