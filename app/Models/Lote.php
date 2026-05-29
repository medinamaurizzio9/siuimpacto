<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lote extends Model
{
    public const ESTADOS = ['disponible', 'reservado', 'vendido', 'bloqueado'];

    protected $fillable = [
        'manzano_id',
        'codigo',
        'superficie',
        'precio',
        'estado',
        'fila',
        'columna',
        'coord_x',
        'coord_y',
        'observaciones',
    ];

    public function manzano(): BelongsTo
    {
        return $this->belongsTo(Manzano::class);
    }

    public function venta(): HasOne
    {
        return $this->hasOne(Venta::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    public function reservaActiva(): HasOne
    {
        return $this->hasOne(Reserva::class)->where('estado', 'activa');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(LotHistory::class);
    }
}
