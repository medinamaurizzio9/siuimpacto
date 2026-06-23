<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrbanizacionReferencia extends Model
{
    protected $table = 'urbanizacion_referencias';

    protected $fillable = [
        'urbanizacion_id',
        'nombre',
        'tipo_referencia',
        'latitud',
        'longitud',
        'plano_x',
        'plano_y',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:8',
            'longitud' => 'decimal:8',
            'plano_x' => 'decimal:3',
            'plano_y' => 'decimal:3',
            'activo' => 'boolean',
        ];
    }

    public function urbanizacion(): BelongsTo
    {
        return $this->belongsTo(Urbanizacion::class);
    }
}
