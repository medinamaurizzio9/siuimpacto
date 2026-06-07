<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\Cuota;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Validation\ValidationException;

class CashMovementService
{
    public function __construct(private AuditService $auditService)
    {
    }

    public function ingresoReserva(Reserva $reserva, float $monto, string $metodoPago, ?User $user, ?string $referencia = null): ?CashMovement
    {
        if ($monto <= 0) {
            return null;
        }

        return $this->create([
            'user_id' => $user?->id,
            'cliente_id' => $reserva->cliente_id,
            'reservation_id' => $reserva->id,
            'tipo' => 'ingreso',
            'concepto' => 'reserva',
            'metodo_pago' => $metodoPago,
            'monto' => $monto,
            'fecha' => now(),
            'referencia' => $referencia,
            'estado' => 'confirmado',
        ]);
    }

    public function ingresoVenta(Venta $venta, float $monto, string $concepto, string $metodoPago, ?User $user, ?string $referencia = null): ?CashMovement
    {
        if ($monto <= 0) {
            return null;
        }

        return $this->create([
            'user_id' => $user?->id,
            'cliente_id' => $venta->cliente_id,
            'sale_id' => $venta->id,
            'tipo' => 'ingreso',
            'concepto' => $concepto,
            'metodo_pago' => $metodoPago,
            'monto' => $monto,
            'fecha' => now(),
            'referencia' => $referencia,
            'estado' => 'confirmado',
        ]);
    }

    public function ingresoCuota(Cuota $cuota, float $monto, string $metodoPago, ?User $user, ?string $referencia = null): CashMovement
    {
        $cuota->loadMissing('venta');

        return $this->create([
            'user_id' => $user?->id,
            'cliente_id' => $cuota->venta->cliente_id,
            'sale_id' => $cuota->venta_id,
            'installment_id' => $cuota->id,
            'tipo' => 'ingreso',
            'concepto' => 'cuota',
            'metodo_pago' => $metodoPago,
            'monto' => $monto,
            'fecha' => now(),
            'referencia' => $referencia,
            'estado' => 'confirmado',
        ]);
    }

    public function create(array $data): CashMovement
    {
        return CashMovement::create($data);
    }

    public function syncInitialSaleMovement(Venta $venta, string $metodoPago, ?User $user, ?string $referencia, string $motivo): array
    {
        $venta->load('cashMovements');
        $initialMovements = $venta->cashMovements
            ->whereNull('installment_id')
            ->whereIn('concepto', ['anticipo', 'contado']);
        $concepto = (int) $venta->numero_cuotas === 0 ? 'contado' : 'anticipo';
        $amount = (int) $venta->numero_cuotas === 0 ? (float) $venta->precio_final : (float) $venta->cuota_inicial;
        $primary = $initialMovements->firstWhere('concepto', $concepto) ?? $initialMovements->first();
        $changes = [];

        if ($amount > 0) {
            if ($primary) {
                $before = $primary->toArray();
                $primary->update([
                    'user_id' => $user?->id,
                    'cliente_id' => $venta->cliente_id,
                    'concepto' => $concepto,
                    'metodo_pago' => $metodoPago,
                    'monto' => $amount,
                    'referencia' => $referencia,
                    'estado' => 'confirmado',
                ]);
                $this->auditService->log($primary, 'movimiento_inicial_venta_actualizado', $motivo, $before, $primary->fresh()->toArray());
                $changes[] = ['accion' => 'actualizado', 'antes' => $before, 'despues' => $primary->fresh()->toArray()];
            } else {
                $primary = $this->ingresoVenta($venta, $amount, $concepto, $metodoPago, $user, $referencia);
                $this->auditService->log($primary, 'movimiento_inicial_venta_creado', $motivo, null, $primary?->toArray());
                $changes[] = ['accion' => 'creado', 'despues' => $primary?->toArray()];
            }
        }

        foreach ($initialMovements->reject(fn (CashMovement $movement): bool => $primary && $movement->id === $primary->id) as $extra) {
            if ($extra->estado !== 'anulado') {
                $before = $extra->toArray();
                $extra->update(['estado' => 'anulado']);
                $this->auditService->log($extra, 'movimiento_inicial_venta_anulado', $motivo, $before, $extra->fresh()->toArray());
                $changes[] = ['accion' => 'anulado', 'antes' => $before, 'despues' => $extra->fresh()->toArray()];
            }
        }

        if ($amount <= 0 && $primary && $primary->estado !== 'anulado') {
            $before = $primary->toArray();
            $primary->update(['estado' => 'anulado']);
            $this->auditService->log($primary, 'movimiento_inicial_venta_anulado', $motivo, $before, $primary->fresh()->toArray());
            $changes[] = ['accion' => 'anulado', 'antes' => $before, 'despues' => $primary->fresh()->toArray()];
        }

        return $changes;
    }

    public function annul(CashMovement $movement, ?string $motivo = null): CashMovement
    {
        if ($movement->estado === 'anulado') {
            throw ValidationException::withMessages(['movimiento' => 'El movimiento de caja ya esta anulado.']);
        }

        if (! $motivo) {
            throw ValidationException::withMessages(['motivo' => 'Debes indicar el motivo de anulacion del movimiento de caja.']);
        }

        $before = $movement->toArray();
        $movement->update(['estado' => 'anulado']);
        $this->auditService->log($movement, 'anular_caja', $motivo, $before, $movement->fresh()->toArray());

        return $movement;
    }
}
