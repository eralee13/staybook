<?php

namespace App\Console;

use App\Services\ExelyImportService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('fx:update')->hourly();
        // Tourmind update booking status
        $schedule->command('app:tm-update-status')->everyMinute();
        // Emerging update booking status
        $schedule->command('app:etg-update-status')->everyMinute();

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\ImportEmergingJsonl::class,
        \App\Console\Commands\ImportEmergingHotels::class,
    ];
}
