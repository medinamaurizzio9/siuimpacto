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
        'saldo_financiar',
        'numero_cuotas',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_venta' => 'date',
            'precio_final' => 'decimal:2',
            'cuota_inicial' => 'decimal:2',
            'saldo_financiar' => 'decimal:2',
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
        return (float) $this->saldo_financiar;
    }
}
