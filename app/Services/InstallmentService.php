<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstallmentService
{
    public function __construct(private CashMovementService $cashMovementService, private AuditService $auditService)
    {
    }

    public function generateForSale(Venta $venta): void
    {
        if ($venta->numero_cuotas < 1) {
            return;
        }

        $saldo = max(0, (float) $venta->precio_final - (float) $venta->cuota_inicial);
        $monto = round($saldo / $venta->numero_cuotas, 2);
        $fechaBase = Carbon::parse($venta->fecha_venta);

        for ($i = 1; $i <= $venta->numero_cuotas; $i++) {
            Cuota::create([
                'venta_id' => $venta->id,
                'numero' => $i,
                'fecha_programada' => $fechaBase->copy()->addMonths($i),
                'fecha_vencimiento' => $fechaBase->copy()->addMonths($i),
                'monto' => $monto,
                'monto_pagado' => 0,
                'saldo_pendiente' => $monto,
                'estado' => 'pendiente',
            ]);
        }
    }

    public function resyncForSale(Venta $venta): array
    {
        $venta->load('cuotas');
        $preserved = $venta->cuotas->filter(fn (Cuota $cuota): bool => (float) $cuota->monto_pagado > 0);

        if ((int) $venta->numero_cuotas < $preserved->count()) {
            throw ValidationException::withMessages([
                'numero_cuotas' => 'El numero de cuotas no puede ser menor a la cantidad de cuotas que ya tienen pagos.',
            ]);
        }

        $toDelete = $venta->cuotas->reject(fn (Cuota $cuota): bool => (float) $cuota->monto_pagado > 0);
        $deleted = $toDelete->map(fn (Cuota $cuota): array => $cuota->toArray())->values()->all();
        Cuota::whereIn('id', $toDelete->pluck('id'))->delete();

        $paidAmount = (float) $preserved->sum('monto_pagado');
        $availableAfterInitial = max(0, round((float) $venta->precio_final - (float) $venta->cuota_inicial, 2));
        if ($paidAmount > $availableAfterInitial) {
            throw ValidationException::withMessages([
                'precio_final' => 'El precio y la cuota inicial no pueden dejar un saldo menor al monto ya pagado en cuotas.',
            ]);
        }

        $remainingBalance = (int) $venta->numero_cuotas === 0
            ? 0.0
            : max(0, round($availableAfterInitial - $paidAmount, 2));
        $preservedOutstanding = (float) $preserved->sum('saldo_pendiente');
        if ($preservedOutstanding > $remainingBalance) {
            throw ValidationException::withMessages([
                'precio_final' => 'El nuevo saldo no puede ser menor al saldo pendiente de las cuotas que ya tienen pagos.',
            ]);
        }

        $balanceToGenerate = max(0, round($remainingBalance - $preservedOutstanding, 2));
        $pendingCount = max(0, (int) $venta->numero_cuotas - $preserved->count());
        $created = [];

        if ($pendingCount > 0 && $balanceToGenerate > 0) {
            $baseAmount = round($balanceToGenerate / $pendingCount, 2);
            $distributed = 0.0;
            $nextNumber = max(0, (int) $preserved->max('numero'));
            $fechaBase = Carbon::parse($venta->fecha_venta);

            for ($i = 1; $i <= $pendingCount; $i++) {
                $number = $nextNumber + $i;
                $amount = $i === $pendingCount
                    ? round($balanceToGenerate - $distributed, 2)
                    : $baseAmount;
                $distributed += $amount;

                $cuota = Cuota::create([
                    'venta_id' => $venta->id,
                    'numero' => $number,
                    'fecha_programada' => $fechaBase->copy()->addMonths($number),
                    'fecha_vencimiento' => $fechaBase->copy()->addMonths($number),
                    'monto' => $amount,
                    'monto_pagado' => 0,
                    'saldo_pendiente' => $amount,
                    'estado' => 'pendiente',
                ]);
                $created[] = $cuota->toArray();
            }
        }

        $venta->update(['saldo_financiar' => $remainingBalance]);

        return [
            'cuotas_conservadas' => $preserved->map(fn (Cuota $cuota): array => $cuota->toArray())->values()->all(),
            'cuotas_eliminadas' => $deleted,
            'cuotas_creadas' => $created,
            'total_pagado_cuotas' => $paidAmount,
            'saldo_financiar' => $remainingBalance,
        ];
    }

    public function pay(Cuota $cuota, float $monto, string $metodoPago, ?User $user, ?string $referencia = null): Cuota
    {
        if ($monto <= 0) {
            throw ValidationException::withMessages(['monto_pagado' => 'El monto a pagar debe ser mayor a cero.']);
        }

        return DB::transaction(function () use ($cuota, $monto, $metodoPago, $user, $referencia): Cuota {
            $cuota->refresh();
            $nuevoPagado = round((float) $cuota->monto_pagado + $monto, 2);

            if ($nuevoPagado > (float) $cuota->monto) {
                throw ValidationException::withMessages(['monto_pagado' => 'El pago supera el saldo pendiente de la cuota.']);
            }

            $saldo = round((float) $cuota->monto - $nuevoPagado, 2);
            $cuota->update([
                'monto_pagado' => $nuevoPagado,
                'saldo_pendiente' => $saldo,
                'fecha_pago' => $saldo <= 0 ? now() : $cuota->fecha_pago,
                'estado' => $saldo <= 0 ? 'pagada' : 'parcial',
            ]);
            $this->auditService->log($cuota, 'cobrar_cuota', 'Pago de cuota registrado.', null, $cuota->fresh()->toArray());

            $this->cashMovementService->ingresoCuota($cuota, $monto, $metodoPago, $user, $referencia);

            return $cuota;
        });
    }

    public function markOverdue(): int
    {
        return Cuota::whereIn('estado', ['pendiente', 'parcial'])
            ->whereDate('fecha_programada', '<', now())
            ->update(['estado' => 'vencida']);
    }
}
