<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\Hotelstar\HotelstarHotelStaticController;

class HotelStarHotelStaticList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hotelstar-hotel-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Hotelstar hotel static list and update or create hotels and room';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(HotelstarHotelStaticController::class);

        $request = new Request();
        $controller->HSHotelStatic($request); // Передаём в метод
        // $this->info('Список отелей и комнаты обновлён.');
    }
}
