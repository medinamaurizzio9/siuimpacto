<?php

namespace App\Services;

use App\Models\CommercialSetting;
use Illuminate\Support\Carbon;

class CommercialSettingsService
{
    public const RESERVA_DIAS_HABILES_ASESOR = 'reserva_dias_habiles_asesor';

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
