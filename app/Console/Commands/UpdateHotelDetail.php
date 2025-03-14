<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateHotelDetail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-hotel-detail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tourmind HotelDetailController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(HotelDetailController::class);

        $request = new Request(); 
        $controller->fetchHotelDetail($request); // Передаём в метод
        $this->info('Список отелей сумма комнаты обновлён.');
    }
}
