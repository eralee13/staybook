<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EtgBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        $username = $request->getUser();
        $password = $request->getPassword();

        $validUser = 'api@mail.com';
        $validPass = 'YN5vYsgh:{5RM#d';

        // 401 + JSON на неверные креды — всё ок
        if (!$username || !$password || $username !== $validUser || $password !== $validPass) {
            return response()->json([
                'code'    => 401,
                'message' => 'Invalid credentials.',
            ], 401, [
                'WWW-Authenticate' => 'Basic',
            ]);
        }

        $response = $next($request);

        // 👇 Для статических отелей с валидными кредами
        // форсируем NDJSON-заголовок
        if (
            $response->getStatusCode() === 200 &&
            $request->is('api/v1.1/hotels', 'api/v1.1/search/hotels')
        ) {
            $response->headers->set(
                'Content-Type',
                'application/x-ndjson; charset=utf-8'
            );
        }

        return $response;
    }
}