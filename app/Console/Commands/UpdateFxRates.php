<?php

namespace App\Console\Commands;

use App\Services\FXService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UpdateFxRates extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'fx:update';
    protected $description = 'Обновить курсы валют из FX.kg';

    public function handle(FXService $fx): void
    {
        $rates = $fx->getCentralRates();
        // кэшируем на 60 минут
        Cache::put('fx.rates', $rates, now()->addHour());
        $this->info('Курсы валют обновлены.');
    }
}
