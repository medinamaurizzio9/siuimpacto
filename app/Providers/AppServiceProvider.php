<?php

namespace App\Providers;

use App\Support\UrbanizacionContext;
use App\Services\SystemSettingsService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view): void {
            $view->with('systemSettings', app(SystemSettingsService::class)->all());
        });

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();

            $view->with([
                'urbanizacionActual' => $user ? UrbanizacionContext::current() : null,
                'urbanizacionesDisponibles' => $user && ! $user->hasRole('cliente') ? UrbanizacionContext::accessibleUrbanizaciones($user) : collect(),
            ]);
        });
    }
}
