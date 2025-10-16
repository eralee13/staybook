<?php
namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next, ...$requiredScopes): Response
    {
        // извлекаем токен из заголовка
        $header = $request->header('X-API-Key') ?: $request->bearerToken();
        if (!$header || !str_contains($header, '.')) {
            return response()->json(['error'=>['code'=>'unauthorized','message'=>'Missing API key']], 401);
        }

        [$prefix, $plain] = explode('.', $header, 2);
        $hash = hash('sha256', $plain);

        $token = ApiToken::where('prefix', $prefix)
            ->where('token_hash', $hash)
            ->first();

        if (!$token) {
            return response()->json(['error'=>['code'=>'unauthorized','message'=>'Invalid API key']], 401);
        }

        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            return response()->json(['error'=>['code'=>'unauthorized','message'=>'API key expired']], 401);
        }

        // проверка скопов (если указаны в роуте)
        if (!empty($requiredScopes)) {
            $granted = collect($token->scopes ?? []);
            foreach ($requiredScopes as $scope) {
                if (!$granted->contains($scope)) {
                    return response()->json(['error'=>['code'=>'forbidden','message'=>"Scope '{$scope}' required"]], 403);
                }
            }
        }

        // прокидываем в request (пригодится в логах/политиках)
        $request->attributes->set('api_token', $token);
        $token->forceFill(['last_used_at' => now()])->saveQuietly();

        \Log::info('partner.api.hit', [
            'token_id' => $token->id,
            'scope'    => $requiredScopes,
            'path'     => $request->path(),
            'ip'       => $request->ip(),
            'ua'       => substr($request->userAgent() ?? '', 0, 200),
        ]);

        return $next($request);
    }
}
