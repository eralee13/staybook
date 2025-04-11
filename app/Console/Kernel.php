<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Tourmind
        $schedule->command('app:tm-hotel-detail')->daily(); 
        $schedule->command('app:tm-hotel-static-list')->daily(); 
        $schedule->command('app:tm-region-list')->daily(); 
        $schedule->command('app:tm-room-static-list')->daily(); 
        // Emerging
        $schedule->command('app:emerging-fetch-hotel-dump')->daily(); 
        
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
