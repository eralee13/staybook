<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Services\Tourmind\HotelServices;

class TMUpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tm-update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Tourmind book status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(HotelServices::class);
        $request = new Request(); // Создаём пустой запрос
        $controller->updateBookingStatuses($request); // Передаём в метод
        // $this->info('Список типов номеров обновлён.');
    }
}
