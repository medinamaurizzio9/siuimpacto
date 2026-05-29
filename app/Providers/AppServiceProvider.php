<?php

namespace App\Providers;

use App\Support\UrbanizacionContext;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();

            $view->with([
                'urbanizacionActual' => $user ? UrbanizacionContext::current() : null,
                'urbanizacionesDisponibles' => $user && ! $user->hasRole('cliente') ? UrbanizacionContext::accessibleUrbanizaciones($user) : collect(),
            ]);
        });
    }
}
