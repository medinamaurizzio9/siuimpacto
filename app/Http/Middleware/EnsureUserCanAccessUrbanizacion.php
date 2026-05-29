<?php

namespace App\Http\Middleware;

use App\Support\UrbanizacionContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessUrbanizacion
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole('cliente')) {
            return $next($request);
        }

        $urbanizacionId = UrbanizacionContext::currentId();

        if ($urbanizacionId && ! UrbanizacionContext::userCanAccess($request->user(), $urbanizacionId)) {
            $request->session()->forget('urbanizacion_id');

            return redirect()
                ->route('urbanizaciones.select')
                ->withErrors(['urbanizacion' => 'No tienes acceso a esta urbanizacion']);
        }

        return $next($request);
    }
}
