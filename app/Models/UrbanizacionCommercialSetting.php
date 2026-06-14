<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UrbanizacionCommercialSetting extends Model
{
    protected $fillable = [
        'urbanizacion_id',
        'reserva_dias_habiles_asesor',
        'tipo_cambio_usd_bs',
        'incremento_credito_tipo',
        'incremento_credito_valor',
    ];

    protected function casts(): array
    {
        return [
            'reserva_dias_habiles_asesor' => 'integer',
            'tipo_cambio_usd_bs' => 'decimal:2',
            'incremento_credito_valor' => 'decimal:2',
        ];
    }

    public function urbanizacion(): BelongsTo
    {
        return $this->belongsTo(Urbanizacion::class);
    }
}
