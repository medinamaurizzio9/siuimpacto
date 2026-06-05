<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'supervisor_comercial_id',
        'grupo_comercial_id',
        'nombre',
        'ci',
        'celular',
        'email',
        'direccion',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supervisorComercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_comercial_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoComercial::class, 'grupo_comercial_id');
    }
}
