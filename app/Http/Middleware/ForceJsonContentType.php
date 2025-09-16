<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonContentType
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->is('api/*')) {
            // Перезаписываем Content-Type без charset
            $response->headers->set('Content-Type', 'application/json', true);
        }

        return $response;
    }
}