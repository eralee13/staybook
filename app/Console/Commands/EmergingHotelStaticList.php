<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\Emerging\EmergingHotelStaticController;

class EmergingHotelStaticList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:emerging-hotel-static-list';

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
        $controller = app(EmergingHotelStaticController::class);

        $request = new Request();
        $controller->fetchHotelStatic($request); // Передаём в метод
        $this->info('Выполнение завершен.');
    }
}
