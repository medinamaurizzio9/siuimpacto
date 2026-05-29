<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private LotService $lotService,
        private InstallmentService $installmentService,
        private CashMovementService $cashMovementService,
        private AuditService $auditService
    ) {
    }

    public function create(array $data, ?User $user): Venta
    {
        return DB::transaction(function () use ($data, $user): Venta {
            $lote = Lote::with('reservaActiva')->lockForUpdate()->findOrFail($data['lote_id']);
            $this->lotService->ensureCanSell($lote, (int) $data['cliente_id'], (bool) ($data['admin_confirma_reserva'] ?? false));

            $reserva = $lote->reservaActiva;
            if ($reserva && $reserva->cliente_id === (int) $data['cliente_id']) {
                $data['reserva_id'] = $reserva->id;
            }

            $venta = Venta::create([
                ...$data,
                'user_id' => $user?->id,
            ]);
            $this->auditService->log($venta, 'crear_venta', 'Venta registrada.', null, $venta->toArray());

            if ($reserva && $venta->reserva_id === $reserva->id) {
                $reserva->update(['estado' => 'convertida']);
                $this->auditService->log($reserva, 'convertir_reserva', 'La reserva fue convertida en venta.', null, $reserva->fresh()->toArray());
            }

            $this->lotService->changeStatus($lote, 'vendido', 'lote_vendido', $user, 'Lote vendido al cliente #'.$venta->cliente_id);
            $this->cashMovementService->ingresoVenta($venta, (float) $venta->cuota_inicial, 'anticipo', $data['metodo_pago'] ?? 'efectivo', $user, $data['referencia'] ?? null);

            if ((int) $venta->numero_cuotas === 0) {
                $this->cashMovementService->ingresoVenta($venta, max(0, (float) $venta->precio_final - (float) $venta->cuota_inicial), 'contado', $data['metodo_pago'] ?? 'efectivo', $user, $data['referencia'] ?? null);
            } else {
                $this->installmentService->generateForSale($venta);
            }

            return $venta;
        });
    }

    public function annul(Venta $venta, ?User $user, ?string $motivo = null): Venta
    {
        return DB::transaction(function () use ($venta, $user, $motivo): Venta {
            if (! $motivo) {
                throw ValidationException::withMessages(['motivo' => 'Debes indicar el motivo para anular la venta.']);
            }

            if ($venta->cuotas()->exists() || $venta->cashMovements()->exists()) {
                throw ValidationException::withMessages(['venta' => 'No se puede anular una venta con cuotas o movimientos de caja asociados.']);
            }

            $before = $venta->toArray();
            $venta->update(['estado' => 'anulada']);
            $this->lotService->changeStatus($venta->lote, 'disponible', 'venta_anulada', $user, 'Venta anulada.');
            $this->auditService->log($venta, 'anular_venta', $motivo, $before, $venta->fresh()->toArray());

            return $venta;
        });
    }
}
