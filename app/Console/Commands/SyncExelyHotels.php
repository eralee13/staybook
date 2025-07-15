<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Rate;

class SyncExelyHotels extends Command
{
    protected $signature = 'sync:exely-hotels';
    protected $description = 'Синхронизация отелей и тарифов из Exely API';

    public function handle()
    {
        try {
            (new \App\Services\ExelyImportService)->handle();
            $this->info('Импорт Exely завершён: ' . now());
        } catch (\Throwable $e) {
            Log::error('Ошибка импорта Exely: ' . $e->getMessage());
            $this->error($e->getMessage());
        }

        $this->info('✅ Exely sync completed at ' . now());
    }
}