<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asesor extends Model
{
    protected $table = 'asesores';

    protected $fillable = [
        'user_id',
        'supervisor_id',
        'grupo_comercial_id',
        'nombre',
        'apellido',
        'ci',
        'celular',
        'email',
        'direccion',
        'grupo_comercial',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoComercial::class, 'grupo_comercial_id');
    }
}
