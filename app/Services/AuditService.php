<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function log(
        ?Model $model,
        string $accion,
        ?string $descripcion = null,
        ?array $anteriores = null,
        ?array $nuevos = null,
        ?Request $request = null
    ): AuditLog {
        $request ??= request();

        return AuditLog::create([
            'user_id' => $request?->user()?->id,
            'modelo' => $model ? class_basename($model) : 'Sistema',
            'modelo_id' => $model?->getKey(),
            'accion' => $accion,
            'descripcion' => $descripcion,
            'datos_anteriores' => $anteriores,
            'datos_nuevos' => $nuevos,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
