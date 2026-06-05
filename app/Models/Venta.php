<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'lote_id',
        'urbanizacion_id',
        'cliente_id',
        'user_id',
        'reserva_id',
        'vendedor_id',
        'supervisor_ventas_id',
        'supervisor_comercial_id',
        'grupo_comercial_id',
        'usuario_creador_id',
        'usuario_actualizador_id',
        'fecha_venta',
        'precio_final',
        'monto_total',
        'cuota_inicial',
        'numero_cuotas',
        'tipo_venta',
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

    public function urbanizacion(): BelongsTo
    {
        return $this->belongsTo(Urbanizacion::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function supervisorVentas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_ventas_id');
    }

    public function supervisorComercial(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_comercial_id');
    }

    public function grupoComercial(): BelongsTo
    {
        return $this->belongsTo(GrupoComercial::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_creador_id');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_actualizador_id');
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
