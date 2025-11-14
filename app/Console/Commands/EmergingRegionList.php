<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Controllers\API\V1\Emerging\EmergingRegionController;

class EmergingRegionList extends Command
{
    /**
     * Имя и параметры консольной команды
     */
    protected $signature = 'app:emerging-region-list
                            {--source= : URL или путь к файлу (JSON или JSONL)}
                            {--only-cities : Загружать только города}';

    protected $description = 'Импорт городов из фида Emerging в таблицу cities';

    /**
     * Выполнение команды
     */
    public function handle()
    {
        /** @var EmergingRegionController $controller */
        $controller = app(EmergingRegionController::class);

        // Формируем искусственный Request, чтобы передать параметры
        $request = new Request([
            'only_cities' => $this->option('only-cities') ?? true,
            'source'      => $this->option('source'),
        ]);

        $resp = $controller->fetchRegionStatic($request);

        $this->info('Импорт завершён.');
        if (method_exists($resp, 'getStatusCode')) {
            $this->line('HTTP '.$resp->getStatusCode());
        }
        if (method_exists($resp, 'getContent')) {
            $this->line($resp->getContent());
        }
    }
}
