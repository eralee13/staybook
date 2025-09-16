<?php

namespace App\Providers;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application amenities.
     */
    public function boot(): void
    {
        // Не трогать Request, если он ещё не инициализирован
        if (!$this->app->runningInConsole() && $this->app->bound('request')) {
            Blade::directive('routeactive', function ($route) {
                return "<?php echo request()?->routeIs($route) ? 'class=\"current\"' : '' ?>";
            });
            Blade::if('hotel', fn() => optional(auth()->user())->hasRole('Hotel'));
            Blade::if('admin', fn() => optional(auth()->user())->hasRole('Super Admin'));
            Blade::if('manager', fn() => optional(auth()->user())->hasRole('Manager'));
            Blade::if('buh', fn() => optional(auth()->user())->hasRole('Accoundate'));
        }
        // Scramble лучше тоже обернуть
        Scramble::configure();

        Scramble::registerApi('v1.0', [
            'api_path' => 'api/v1.0',
            'info' => ['version' => '1.0'],
            'servers' => [
                'Local v1.0' => '/api/v1.0',
            ],
        ])->expose(
            ui: '/docs/v1.0/api',
            document: '/docs/v1.0/openapi.json',
        );

        Scramble::registerApi('v1.1', [
            'api_path' => 'api/v1.1',
            'info' => ['version' => '1.1'],
            'servers' => [
                'Local v1.1' => '/api/v1.1',
            ],
        ])
            // Добавляем BasicAuth только для v1.1, чтобы появилась кнопка Authorize
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('basic'),
                    'basicAuth'
                );
            })
            ->expose(
                ui: '/docs/v1.1/api',
                document: '/docs/v1.1/openapi.json',
            );
    }
}
