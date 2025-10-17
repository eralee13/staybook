<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('routeactive', function ($route) {
            return "<?php echo request()?->routeIs($route) ? 'class=\"current\"' : '' ?>";
        });

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                // ✅ apiKey-схема: имя заголовка и где его искать
                $openApi->secure(
                    SecurityScheme::apiKey('X-API-Key', 'header')
                );
            });
    }
}
