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
