<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\Emerging\EmergingRegionController;

class EmergingHotelList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:emerging-hotel-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(EmergingHotelController::class);

        $request = new Request();
        $controller->fetchHotelStatic($request); // Передаём в метод
        $this->info('Список отелей и комнаты обновлён.');
    }
}
