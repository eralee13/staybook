<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\Emerging\EmergingFormController;

class EmergingUpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:etg-update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emerging get success booking status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(EmergingFormController::class);

        $request = new Request();
        $controller->updateBookingStatuses($request); // Передаём в метод
        // $this->info('Список броней обновлён.');
    }
}
