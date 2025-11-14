<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public function send(string $text, ?string $chatId = null): bool
    {
        $token  = (string) config('services.telegram.bot_token');
        $chatId = $chatId ?: (string) config('services.telegram.chat_id');

        if (!$token || !$chatId) {
            Log::warning('TelegramNotifier: missing token or chat_id');
            return false;
        }

        try {
            $resp = Http::asForm()
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text'    => $text,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if (!$resp->successful()) {
                Log::warning('TelegramNotifier failed', [
                    'status' => $resp->status(),
                    'body'   => $resp->body(),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('TelegramNotifier exception: '.$e->getMessage());
            return false;
        }
    }

    public static function fmt(string $label, array $kv): string
    {
        $lines = ["<b>{$label}</b>"];
        foreach ($kv as $k => $v) {
            $v = is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE);
            $lines[] = "<b>{$k}:</b> {$v}";
        }
        return implode("\n", $lines);
    }
}