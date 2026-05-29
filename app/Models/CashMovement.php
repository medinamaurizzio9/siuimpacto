<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    public const TIPOS = ['ingreso', 'egreso'];
    public const CONCEPTOS = ['reserva', 'anticipo', 'contado', 'cuota', 'ajuste'];
    public const METODOS = ['efectivo', 'transferencia', 'QR', 'banco', 'otro'];

    protected $fillable = [
        'user_id',
        'cliente_id',
        'sale_id',
        'reservation_id',
        'installment_id',
        'tipo',
        'concepto',
        'metodo_pago',
        'monto',
        'fecha',
        'referencia',
        'observaciones',
        'estado',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'sale_id');
    }

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reservation_id');
    }

    public function cuota(): BelongsTo
    {
        return $this->belongsTo(Cuota::class, 'installment_id');
    }
}
