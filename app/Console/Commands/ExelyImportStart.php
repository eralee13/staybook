<?php
// app/Console/Commands/ExelyImportStart.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Exely\FetchPropertyIdsJob;

class ExelyImportStart extends Command
{
    protected $signature = 'exely:import';
    protected $description = 'Start queued Exely import';

    public function handle(): int
    {
        dispatch(new FetchPropertyIdsJob())->onQueue('imports');
        $this->info('Import queued.');
        return self::SUCCESS;
    }
}