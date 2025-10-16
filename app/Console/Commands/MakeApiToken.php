<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiToken;
use Illuminate\Support\Str;

class MakeApiToken extends Command
{
    protected $signature = 'api-token:make {name} {--scopes=*} {--partner_id=} {--days=365}';
    protected $description = 'Создать API токен (X-API-Key)';

    public function handle()
    {
        $plain = bin2hex(random_bytes(24));          // 48 hex символов
        $prefix = Str::upper(Str::random(8));
        $hash = hash('sha256', $plain);

        $token = ApiToken::create([
            'name'       => $this->argument('name'),
            'prefix'     => $prefix,
            'token_hash' => $hash,
            'scopes'     => $this->option('scopes') ?: ['catalog.read','availability.read'],
            'partner_id' => $this->option('partner_id'),
            'expires_at' => now()->addDays((int)$this->option('days')),
        ]);

        $this->info('Создан токен:');
        $this->line("Name:     {$token->name}");
        $this->line("Scopes:   ".json_encode($token->scopes));
        $this->line("Expires:  {$token->expires_at}");
        $this->newLine();
        $this->warn('Сохраните это значение, его нельзя будет показать снова:');
        $this->line("X-API-Key: {$prefix}.{$plain}");
        return self::SUCCESS;
    }
}
