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

            $data['saldo_financiar'] = (int) ($data['numero_cuotas'] ?? 0) === 0
                ? 0
                : max(0, (float) $data['precio_final'] - (float) ($data['cuota_inicial'] ?? 0));
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

            if ((int) $venta->numero_cuotas === 0) {
                $this->cashMovementService->ingresoVenta($venta, (float) $venta->precio_final, 'contado', $data['metodo_pago'] ?? 'efectivo', $user, $data['referencia'] ?? null);
            } else {
                $this->cashMovementService->ingresoVenta($venta, (float) $venta->cuota_inicial, 'anticipo', $data['metodo_pago'] ?? 'efectivo', $user, $data['referencia'] ?? null);
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

    public function update(Venta $venta, array $data, ?User $user, string $motivo): Venta
    {
        return DB::transaction(function () use ($venta, $data, $user, $motivo): Venta {
            if (trim($motivo) === '') {
                throw ValidationException::withMessages(['motivo_cambio' => 'Debes explicar el motivo del cambio de esta venta.']);
            }

            $venta = Venta::with('cuotas.cashMovements', 'cashMovements', 'lote')->lockForUpdate()->findOrFail($venta->id);
            $before = $venta->toArray();
            $before['cuotas_antes'] = $venta->cuotas->map(fn ($cuota): array => $cuota->toArray())->all();
            $oldLot = $venta->lote;
            $newLot = Lote::with('reservaActiva')->lockForUpdate()->findOrFail($data['lote_id']);

            if ($venta->estado !== $data['estado'] && ($venta->estado === 'anulada' || $data['estado'] === 'anulada')) {
                throw ValidationException::withMessages([
                    'estado' => 'La anulacion de una venta debe realizarse mediante la accion Anular.',
                ]);
            }

            $installmentFields = ['precio_final', 'cuota_inicial', 'numero_cuotas', 'fecha_venta'];
            $changesInstallmentStructure = collect($installmentFields)->contains(
                fn (string $field): bool => (string) $venta->{$field} !== (string) ($data[$field] ?? $venta->{$field})
            );

            if ($newLot->id !== $oldLot->id) {
                $this->lotService->ensureCanSell($newLot, (int) $data['cliente_id'], (bool) ($data['admin_confirma_reserva'] ?? false));
            }

            $venta->update(collect($data)->except(['metodo_pago', 'referencia', 'admin_confirma_reserva', 'motivo_cambio'])->all());
            $installmentChanges = $changesInstallmentStructure
                ? $this->installmentService->resyncForSale($venta->fresh())
                : [
                    'cuotas_conservadas' => [],
                    'cuotas_eliminadas' => [],
                    'cuotas_creadas' => [],
                    'total_pagado_cuotas' => (float) $venta->cuotas->sum('monto_pagado'),
                    'saldo_financiar' => (int) $venta->numero_cuotas === 0
                        ? 0
                        : max(0, (float) $venta->precio_final - (float) $venta->cuota_inicial - (float) $venta->cuotas->sum('monto_pagado')),
                ];
            $venta->update(['saldo_financiar' => $installmentChanges['saldo_financiar']]);
            $cashChanges = $this->cashMovementService->syncInitialSaleMovement(
                $venta->fresh(),
                $data['metodo_pago'] ?? 'efectivo',
                $user,
                $data['referencia'] ?? null,
                $motivo
            );

            if ($oldLot->id !== $newLot->id) {
                $this->lotService->syncStatusFromReservations($oldLot, 'venta_actualizada', $user, 'La venta fue trasladada a otro lote.');
            }

            if ($venta->estado === 'anulada') {
                $this->lotService->syncStatusFromReservations($newLot, 'venta_actualizada', $user, 'La venta fue marcada como anulada.');
            } else {
                $this->lotService->changeStatus($newLot, 'vendido', 'venta_actualizada', $user, 'Lote asociado a venta actualizada.');
            }

            $after = $venta->fresh()->toArray();
            $after['motivo_cambio'] = $motivo;
            $after['venta_id'] = $venta->id;
            $after['cuotas_conservadas'] = $installmentChanges['cuotas_conservadas'];
            $after['cuotas_eliminadas'] = $installmentChanges['cuotas_eliminadas'];
            $after['cuotas_creadas'] = $installmentChanges['cuotas_creadas'];
            $after['movimientos_caja_actualizados'] = $cashChanges;
            $this->auditService->log($venta, 'venta_actualizada', $motivo, $before, $after);

            return $venta->fresh();
        });
    }
}
