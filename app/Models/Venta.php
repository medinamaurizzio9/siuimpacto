<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'lote_id',
        'cliente_id',
        'user_id',
        'reserva_id',
        'fecha_venta',
        'precio_final',
        'cuota_inicial',
        'numero_cuotas',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_venta' => 'date',
        ];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(Cuota::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'sale_id');
    }

    public function saldo(): float
    {
        return max(0, (float) $this->precio_final - (float) $this->cuota_inicial - (float) $this->cuotas->sum('monto_pagado'));
    }
}
