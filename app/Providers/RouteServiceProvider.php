<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */

    public const HOME = '/profile';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });


        RateLimiter::for('partner-api', function (Request $request) {
            $token = $request->attributes->get('api_token'); // прокинулось из ApiTokenAuth
            $bucketId = $token ? 'api:' . $token->id : 'api:guest:' . $request->ip();

            return [
                Limit::perMinute(600)->by($bucketId)
                    ->response(function () {
                        return response()->json([
                            'error' => [
                                'code' => 'rate_limited',
                                'message' => 'Too Many Attempts. Please retry later.'
                            ]
                        ], 429);
                    }),
            ];
        });
    }
}