<?php
// app/Jobs/Exely/ImportPropertyChunkJob.php
namespace App\Jobs\Exely;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};

class ImportPropertyChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var string[] */
    public array $ids;

    public $tries   = 3;
    public $backoff = [2, 10, 30];

    public function __construct(array $ids) { $this->ids = $ids; }

    public function handle(): void
    {
        foreach ($this->ids as $id) {
            ImportSinglePropertyJob::dispatch($id)->onQueue('imports');
        }
    }
}