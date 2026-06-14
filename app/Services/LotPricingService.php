<?php

namespace App\Services;

use App\Models\Lote;

class LotPricingService
{
    public function __construct(private CommercialSettingsService $settings)
    {
    }

    public function baseUsd(Lote $lote): float
    {
        return round((float) $lote->precio, 2);
    }

    public function creditIncrementUsd(Lote $lote): float
    {
        $base = $this->baseUsd($lote);
        $urbanizacionId = $this->urbanizacionId($lote);

        return $this->settings->incrementoCreditoTipo($urbanizacionId) === 'porcentaje'
            ? round($base * $this->settings->incrementoCreditoValor($urbanizacionId) / 100, 2)
            : round($this->settings->incrementoCreditoValor($urbanizacionId), 2);
    }

    public function creditUsd(Lote $lote): float
    {
        return round($this->baseUsd($lote) + $this->creditIncrementUsd($lote), 2);
    }

    public function operationUsd(Lote $lote, ?string $tipoOperacion): float
    {
        return $tipoOperacion === 'credito'
            ? $this->creditUsd($lote)
            : $this->baseUsd($lote);
    }

    public function bs(float $usd, ?Lote $lote = null): float
    {
        return round($usd * $this->settings->tipoCambioUsdBs($lote ? $this->urbanizacionId($lote) : null), 2);
    }

    public function initialUsd(Lote $lote, float $contextPriceUsd): float
    {
        if ($lote->cuota_inicial_tipo === 'porcentaje') {
            return round($contextPriceUsd * (float) $lote->cuota_inicial_valor / 100, 2);
        }

        return round((float) $lote->cuota_inicial_valor, 2);
    }

    public function formatUsd(float $amount): string
    {
        return '$us '.number_format($amount, 2);
    }

    public function formatBs(float $amount): string
    {
        return 'Bs '.number_format($amount, 2);
    }

    public function payload(Lote $lote): array
    {
        $base = $this->baseUsd($lote);
        $credit = $this->creditUsd($lote);

        return [
            'base_usd' => $base,
            'base_bs' => $this->bs($base, $lote),
            'credit_usd' => $credit,
            'credit_bs' => $this->bs($credit, $lote),
            'credit_increment_usd' => $this->creditIncrementUsd($lote),
            'tipo_cambio_usd_bs' => $this->settings->tipoCambioUsdBs($this->urbanizacionId($lote)),
            'incremento_credito_tipo' => $this->settings->incrementoCreditoTipo($this->urbanizacionId($lote)),
            'incremento_credito_valor' => $this->settings->incrementoCreditoValor($this->urbanizacionId($lote)),
            'initial_credit_usd' => $this->initialUsd($lote, $credit),
            'initial_credit_bs' => $this->bs($this->initialUsd($lote, $credit), $lote),
            'initial_base_usd' => $this->initialUsd($lote, $base),
            'initial_base_bs' => $this->bs($this->initialUsd($lote, $base), $lote),
        ];
    }

    private function urbanizacionId(Lote $lote): ?int
    {
        return $lote->manzano?->urbanizacion_id
            ?? $lote->manzano()->value('urbanizacion_id');
    }
}
