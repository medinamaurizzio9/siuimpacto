<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lote extends Model
{
    public const ESTADOS = ['disponible', 'reservado', 'vendido', 'bloqueado'];
    public const CUOTA_INICIAL_TIPOS = ['monto', 'porcentaje'];

    protected $fillable = [
        'manzano_id',
        'codigo',
        'superficie',
        'precio',
        'cuota_inicial_tipo',
        'cuota_inicial_valor',
        'estado',
        'fila',
        'columna',
        'coord_x',
        'coord_y',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cuota_inicial_valor' => 'decimal:2',
        ];
    }

    public function cuotaInicialTexto(): string
    {
        if ($this->cuota_inicial_tipo === 'porcentaje') {
            $valor = rtrim(rtrim(number_format((float) $this->cuota_inicial_valor, 2, '.', ''), '0'), '.');

            return $valor.'%';
        }

        return 'Bs '.number_format((float) $this->cuota_inicial_valor, 2);
    }

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
