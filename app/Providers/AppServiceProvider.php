<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

// ✅ Добавили нужный импорт генератора
use Dedoc\Scramble\Support\Generator\OpenApiGenerator;


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

        if (class_exists(\Dedoc\Scramble\Scramble::class)) {
            \Dedoc\Scramble\Scramble::configure(); // допустимо, но не требуется
        }



    }
}
