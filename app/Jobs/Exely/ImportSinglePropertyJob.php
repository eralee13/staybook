<?php
// app/Jobs/Exely/ImportSinglePropertyJob.php
namespace App\Jobs\Exely;

use App\Services\ExelyImporter; // ниже покажу
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Cache, Log};
use Illuminate\Support\Str;

class ImportSinglePropertyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $propertyId;
    public $tries   = 5;
    public $backoff = [2, 5, 10, 20, 40];

    public function __construct(string $propertyId)
    {
        $this->propertyId = $propertyId;
        $this->onQueue('imports');
    }

    public function handle(ExelyImporter $importer): void
    {
        // Блокируем конкретный id, чтобы не дублировать
        $lock = cache()->lock("lock:exely:{$this->propertyId}", 300);
        if (!$lock->get()) {
            $this->release(10);
            return;
        }
        try {
            $importer->importProperty($this->propertyId);
            Cache::increment('exely:done');
        } catch (\Throwable $e) {
            Log::error('ImportSingleProperty failed', [
                'id'  => $this->propertyId,
                'err' => $e->getMessage(),
                'ref' => (string) Str::uuid(),
            ]);
            $this->release(30);
        } finally {
            optional($lock)->release();
        }
    }
}