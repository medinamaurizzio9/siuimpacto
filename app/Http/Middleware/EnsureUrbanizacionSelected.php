<?php

namespace App\Http\Middleware;

use App\Support\UrbanizacionContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUrbanizacionSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole('cliente')) {
            return $next($request);
        }

        if (! UrbanizacionContext::currentId()) {
            return redirect()
                ->route('urbanizaciones.select')
                ->with('status', 'Selecciona una urbanizacion para continuar.');
        }

        return $next($request);
    }
}
