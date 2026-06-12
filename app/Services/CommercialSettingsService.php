<?php

namespace App\Services;

use App\Models\CommercialSetting;
use Illuminate\Support\Carbon;

class CommercialSettingsService
{
    public const RESERVA_DIAS_HABILES_ASESOR = 'reserva_dias_habiles_asesor';
    public const TIPO_CAMBIO_USD_BS = 'tipo_cambio_usd_bs';
    public const INCREMENTO_CREDITO_TIPO = 'incremento_credito_tipo';
    public const INCREMENTO_CREDITO_VALOR = 'incremento_credito_valor';

    public function reservaDiasHabilesAsesor(): int
    {
        return max(1, (int) CommercialSetting::query()
            ->where('key', self::RESERVA_DIAS_HABILES_ASESOR)
            ->value('value') ?: 5);
    }

    public function setReservaDiasHabilesAsesor(int $days): CommercialSetting
    {
        return CommercialSetting::updateOrCreate(
            ['key' => self::RESERVA_DIAS_HABILES_ASESOR],
            ['value' => (string) max(1, $days)]
        );
    }

    public function tipoCambioUsdBs(): float
    {
        return max(0, (float) (CommercialSetting::query()
            ->where('key', self::TIPO_CAMBIO_USD_BS)
            ->value('value') ?: 6.96));
    }

    public function incrementoCreditoTipo(): string
    {
        $tipo = CommercialSetting::query()
            ->where('key', self::INCREMENTO_CREDITO_TIPO)
            ->value('value') ?: 'monto';

        return in_array($tipo, ['monto', 'porcentaje'], true) ? $tipo : 'monto';
    }

    public function incrementoCreditoValor(): float
    {
        return max(0, (float) (CommercialSetting::query()
            ->where('key', self::INCREMENTO_CREDITO_VALOR)
            ->value('value') ?: 0));
    }

    public function priceSettings(): array
    {
        return [
            'tipo_cambio_usd_bs' => $this->tipoCambioUsdBs(),
            'incremento_credito_tipo' => $this->incrementoCreditoTipo(),
            'incremento_credito_valor' => $this->incrementoCreditoValor(),
        ];
    }

    public function setPriceSettings(array $data): void
    {
        foreach ([self::TIPO_CAMBIO_USD_BS, self::INCREMENTO_CREDITO_TIPO, self::INCREMENTO_CREDITO_VALOR] as $key) {
            CommercialSetting::updateOrCreate(['key' => $key], ['value' => (string) $data[$key]]);
        }
    }

    public function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $current = $date->copy();
        $added = 0;

        while ($added < $days) {
            $current->addDay();
            if (! $current->isWeekend()) {
                $added++;
            }
        }

        return $current;
    }
}
