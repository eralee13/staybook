<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;

class ForceJsonCharset
{
    public function handle($request, Closure $next)
    {
        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        }

        return $response;
    }
}