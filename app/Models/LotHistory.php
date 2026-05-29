<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotHistory extends Model
{
    protected $fillable = [
        'lote_id',
        'user_id',
        'accion',
        'descripcion',
        'estado_anterior',
        'estado_nuevo',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
