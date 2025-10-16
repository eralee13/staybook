<?php
// app/Jobs/Exely/FetchPropertyIdsJob.php
namespace App\Jobs\Exely;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Cache, Http, Log};

class FetchPropertyIdsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 5;
    public $backoff = [2, 5, 10, 20, 40];

    public function handle(): void
    {
        $base = rtrim(config('services.exely.base_url'), '/');
        $resp = Http::timeout(60)
            ->connectTimeout(10)
            ->retry(5, 300, throw: false)
            ->withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept'    => 'application/json',
            ])
            ->get("$base/content/v1/properties");

        if (!$resp->successful()) {
            Log::warning('Exely properties list failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            $this->release(30);
            return;
        }
        $ids = collect($resp->object()->properties ?? [])
            ->pluck('id')->filter()->map(fn($v)=>(string)$v)->values();

        // сохраним для прогресса
        Cache::put('exely:total', $ids->count(), 3600);
        Cache::put('exely:done', 0, 3600);

        // чанкуем и диспатчим
        $ids->chunk(100)->each(function($chunk){
            dispatch(new ImportPropertyChunkJob($chunk->all()))->onQueue('imports');
        });
    }
}