<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reserva extends Model
{
    public const TIPOS_OPERACION = ['contado', 'credito', 'semicontado'];

    protected $fillable = [
        'cliente_id',
        'lote_id',
        'usuario_id',
        'fecha_reserva',
        'fecha_vencimiento',
        'monto_reserva',
        'estado',
        'tipo_operacion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_reserva' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'reservation_id');
    }
}
