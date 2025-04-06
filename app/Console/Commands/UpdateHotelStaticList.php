<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\Tourmind\HotelStaticListController;

class UpdateHotelStaticList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tm-hotel-static-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление списка отелей из HotelStaticListController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(HotelStaticListController::class);

        $request = new Request(); // Создаём пустой запрос
        $controller->fetchHotels($request); // Передаём в метод
        $this->info('Список отелей обновлён.');
    }
}
