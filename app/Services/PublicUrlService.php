<?php

namespace App\Services;

class PublicUrlService
{
    public function __construct(private SystemSettingsService $settings)
    {
    }

    public function baseUrl(): string
    {
        $configured = trim((string) $this->settings->get('public_base_url'));
        $base = $configured !== '' ? $configured : (string) config('app.url');

        return rtrim($base, '/');
    }

    public function route(string $name, mixed $parameters = []): string
    {
        $path = route($name, $parameters, false);

        return $this->url($path);
    }

    public function url(string $path): string
    {
        return $this->baseUrl().'/'.ltrim($path, '/');
    }
}
