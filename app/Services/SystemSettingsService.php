<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemSettingsService
{
    public const KEYS = [
        'system_name',
        'system_subtitle',
        'logo_main',
        'logo_login',
        'logo_pdf',
        'company_name',
        'razon_social',
        'nit',
        'direccion',
        'ciudad',
        'departamento',
        'telefono',
        'celular',
        'whatsapp',
        'email',
        'website',
        'footer_text',
        'primary_color',
        'secondary_color',
    ];

    public function all(): array
    {
        return Cache::remember('system_settings.all', 60, function () {
            $settings = Schema::hasTable('system_settings')
                ? SystemSetting::query()->pluck('value', 'key')->all()
                : [];

            return [
                'system_name' => $settings['system_name'] ?? 'IMPACTO URBANIZACIONES',
                'system_subtitle' => $settings['system_subtitle'] ?? 'Sistema Integral de Terrenos',
                'logo_main' => $settings['logo_main'] ?? '',
                'logo_login' => $settings['logo_login'] ?? '',
                'logo_pdf' => $settings['logo_pdf'] ?? '',
                'company_name' => $settings['company_name'] ?? 'IMPACTO URBANIZACIONES',
                'razon_social' => $settings['razon_social'] ?? 'IMPACTO URBANIZACIONES',
                'nit' => $settings['nit'] ?? '',
                'direccion' => $settings['direccion'] ?? '',
                'ciudad' => $settings['ciudad'] ?? '',
                'departamento' => $settings['departamento'] ?? '',
                'telefono' => $settings['telefono'] ?? '',
                'celular' => $settings['celular'] ?? '',
                'whatsapp' => $settings['whatsapp'] ?? '',
                'email' => $settings['email'] ?? '',
                'website' => $settings['website'] ?? '',
                'footer_text' => $settings['footer_text'] ?? 'Version piloto - MVP funcional.',
                'primary_color' => $settings['primary_color'] ?? '#0f766e',
                'secondary_color' => $settings['secondary_color'] ?? '#0f2530',
            ];
        });
    }

    public function get(string $key): ?string
    {
        return $this->all()[$key] ?? null;
    }

    public function setMany(array $data): void
    {
        foreach (self::KEYS as $key) {
            if (array_key_exists($key, $data)) {
                SystemSetting::updateOrCreate(['key' => $key], ['value' => $data[$key]]);
            }
        }

        Cache::forget('system_settings.all');
    }
}
