<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\Tourmind\RegionListController;

class UpdateRegionList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotel:update-region-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление списка регионов из RegionListController';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(RegionListController::class);

        $request = new Request(); // Создаём пустой запрос
        $controller->fetchRegions($request); // Передаём в метод
        $this->info('Список регионов обновлён.');
    }
}
