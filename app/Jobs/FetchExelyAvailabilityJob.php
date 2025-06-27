<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

class FetchExelyAvailabilityJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    protected string $exelyId;
    protected Carbon $startDate;
    protected Carbon $endDate;

    public function __construct(string $exelyId, Carbon $startDate, Carbon $endDate)
    {
        $this->exelyId = $exelyId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function middleware()
    {
        return [
            new ThrottlesExceptions(5, 1)
        ];
    }

    public function handle(): void
    {
        $results = [];
        $period = $this->startDate->daysUntil($this->endDate);

        foreach ($period as $date) {
            $params = [
                'arrivalDate' => $date->format('Y-m-d'),
                'departureDate' => $date->copy()->addDay()->format('Y-m-d'),
                'adults' => 1,
                'includeExtraStays' => 'false',
                'includeExtraServices' => 'false',
            ];

            $query = http_build_query($params);
            $url = rtrim(config('services.exely.base_url'), '/') . "/search/v1/properties/{$this->exelyId}/room-stays?{$query}";

            try {
                $response = Http::withHeaders([
                    'x-api-key' => config('services.exely.key'),
                    'accept' => 'application/json',
                ])->timeout(10)->get($url);

                Log::debug('📤 Exely API call', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['roomStays']) && is_array($data['roomStays'])) {
                        foreach ($data['roomStays'] as $roomStay) {
                            if (!isset($roomStay['id'])) continue;

                            $results[] = [
                                'rate_id' => $roomStay['id'],
                                'room_id' => $roomStay['roomId'] ?? ('rate_' . $roomStay['id']),
                                'rate_name' => $roomStay['ratePlan']['name'] ?? ('Rate #' . $roomStay['id']),
                                'availability' => isset($roomStay['availability']) ? (int) $roomStay['availability'] : 0,
                                'date' => $date->format('Y-m-d'),
                            ];
                        }
                    }
                } else {
                    Log::warning('Exely API error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url' => $url,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('❌ Exely API request failed', [
                    'exception' => $e->getMessage(),
                    'url' => $url,
                ]);
            }
        }

        $cacheKey = "exely_availability_{$this->exelyId}";
        Cache::put($cacheKey, $results, now()->addMinutes(15));

        Log::debug('✅ Exely данные закэшированы', [
            'key' => $cacheKey,
            'count' => count($results)
        ]);
    }
}