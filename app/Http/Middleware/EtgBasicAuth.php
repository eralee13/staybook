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

        // РЕКОМЕНДАЦИЯ: не используйте app('etg.auth') внутри — это ещё один биндинг.
        // Проще сравнить с env/конфигом:
        $validUser = 'api@mail.com';
        $validPass = 'YN5vYsgh:{5RM#d';

        if (!$username || !$password || $username !== $validUser || $password !== $validPass) {
            return response()->json([
                'code'    => 401,
                'message' => 'Invalid credentials.',
            ], 401, [
                'WWW-Authenticate' => 'Basic',
            ]);
        }

        return $next($request);
    }
}