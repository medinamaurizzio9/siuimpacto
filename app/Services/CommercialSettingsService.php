<?php

namespace App\Services;

use App\Models\CommercialSetting;
use App\Models\Urbanizacion;
use App\Models\UrbanizacionCommercialSetting;
use App\Support\UrbanizacionContext;
use Illuminate\Support\Carbon;

class CommercialSettingsService
{
    public const RESERVA_DIAS_HABILES_ASESOR = 'reserva_dias_habiles_asesor';
    public const TIPO_CAMBIO_USD_BS = 'tipo_cambio_usd_bs';
    public const INCREMENTO_CREDITO_TIPO = 'incremento_credito_tipo';
    public const INCREMENTO_CREDITO_VALOR = 'incremento_credito_valor';

    public function reservaDiasHabilesAsesor(?int $urbanizacionId = null): int
    {
        return max(1, $this->settings($urbanizacionId)['reserva_dias_habiles_asesor']);
    }

    public function setReservaDiasHabilesAsesor(int $days, ?int $urbanizacionId = null): UrbanizacionCommercialSetting
    {
        $setting = $this->settingModel($urbanizacionId);
        $setting->update(['reserva_dias_habiles_asesor' => max(1, $days)]);

        return $setting;
    }

    public function tipoCambioUsdBs(?int $urbanizacionId = null): float
    {
        return max(0, (float) $this->settings($urbanizacionId)['tipo_cambio_usd_bs']);
    }

    public function incrementoCreditoTipo(?int $urbanizacionId = null): string
    {
        $tipo = $this->settings($urbanizacionId)['incremento_credito_tipo'];

        return in_array($tipo, ['monto', 'porcentaje'], true) ? $tipo : 'monto';
    }

    public function incrementoCreditoValor(?int $urbanizacionId = null): float
    {
        return max(0, (float) $this->settings($urbanizacionId)['incremento_credito_valor']);
    }

    public function priceSettings(?int $urbanizacionId = null): array
    {
        return [
            'tipo_cambio_usd_bs' => $this->tipoCambioUsdBs($urbanizacionId),
            'incremento_credito_tipo' => $this->incrementoCreditoTipo($urbanizacionId),
            'incremento_credito_valor' => $this->incrementoCreditoValor($urbanizacionId),
        ];
    }

    public function setPriceSettings(array $data, ?int $urbanizacionId = null): UrbanizacionCommercialSetting
    {
        $setting = $this->settingModel($urbanizacionId);
        $setting->update([
            'tipo_cambio_usd_bs' => max(0, (float) $data[self::TIPO_CAMBIO_USD_BS]),
            'incremento_credito_tipo' => in_array($data[self::INCREMENTO_CREDITO_TIPO], ['monto', 'porcentaje'], true)
                ? $data[self::INCREMENTO_CREDITO_TIPO]
                : 'monto',
            'incremento_credito_valor' => max(0, (float) $data[self::INCREMENTO_CREDITO_VALOR]),
        ]);

        return $setting;
    }

    public function updateForUrbanizacion(int $urbanizacionId, array $data): UrbanizacionCommercialSetting
    {
        $setting = $this->settingModel($urbanizacionId);
        $setting->update([
            'reserva_dias_habiles_asesor' => max(1, (int) $data[self::RESERVA_DIAS_HABILES_ASESOR]),
            'tipo_cambio_usd_bs' => max(0, (float) $data[self::TIPO_CAMBIO_USD_BS]),
            'incremento_credito_tipo' => $data[self::INCREMENTO_CREDITO_TIPO],
            'incremento_credito_valor' => max(0, (float) $data[self::INCREMENTO_CREDITO_VALOR]),
        ]);

        return $setting;
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

    public function settings(?int $urbanizacionId = null): array
    {
        $urbanizacionId ??= UrbanizacionContext::currentId();

        if (! $urbanizacionId) {
            return $this->globalDefaults();
        }

        $setting = $this->settingModel($urbanizacionId);

        return [
            'reserva_dias_habiles_asesor' => max(1, (int) $setting->reserva_dias_habiles_asesor),
            'tipo_cambio_usd_bs' => max(0, (float) $setting->tipo_cambio_usd_bs),
            'incremento_credito_tipo' => in_array($setting->incremento_credito_tipo, ['monto', 'porcentaje'], true) ? $setting->incremento_credito_tipo : 'monto',
            'incremento_credito_valor' => max(0, (float) $setting->incremento_credito_valor),
        ];
    }

    public function settingModel(?int $urbanizacionId = null): UrbanizacionCommercialSetting
    {
        $urbanizacionId ??= UrbanizacionContext::currentId();
        abort_unless($urbanizacionId && Urbanizacion::whereKey($urbanizacionId)->exists(), 404, 'Urbanizacion no encontrada.');

        return UrbanizacionCommercialSetting::firstOrCreate(
            ['urbanizacion_id' => $urbanizacionId],
            $this->globalDefaults()
        );
    }

    private function globalDefaults(): array
    {
        $global = CommercialSetting::query()->pluck('value', 'key');
        $tipo = $global[self::INCREMENTO_CREDITO_TIPO] ?? 'monto';

        return [
            'reserva_dias_habiles_asesor' => max(1, (int) ($global[self::RESERVA_DIAS_HABILES_ASESOR] ?? 5)),
            'tipo_cambio_usd_bs' => max(0, (float) ($global[self::TIPO_CAMBIO_USD_BS] ?? 6.96)),
            'incremento_credito_tipo' => in_array($tipo, ['monto', 'porcentaje'], true) ? $tipo : 'monto',
            'incremento_credito_valor' => max(0, (float) ($global[self::INCREMENTO_CREDITO_VALOR] ?? 0)),
        ];
    }
}
