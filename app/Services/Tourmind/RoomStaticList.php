<?php
namespace App\Services\Tourmind;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
class RoomStaticList
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    protected string $tm_agent_code;
    protected string $tm_user_name;
    protected string $tm_password;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');
    }

    public function fetchRoomStaticList(int $pageIndex = 1, int $pageSize = 50): array
    {
        $base = rtrim((string) config('services.tourmind.api_url'), '/'); // https://tmsapi.tourmind.cn/v2
        $url  = $base.'/RoomStaticList';

        // меньше records → быстрее ответ
        $pageIndex = max(1, $pageIndex);
        $pageSize  = min(max(1, $pageSize), 100);

        $payload = [
            'Pagination' => ['PageIndex' => $pageIndex, 'PageSize' => $pageSize],
            'RequestHeader' => [
                'AgentCode'   => config('services.tourmind.agent'),
                'UserName'    => config('services.tourmind.username'),
                'Password'    => config('services.tourmind.password'),
                'RequestTime' => now()->format('Y-m-d H:i:s'),
            ],
        ];
        try {
            $resp = Http::withOptions([
                'force_ip_resolve' => 'v4',
                'curl' => [
                    CURLOPT_IPRESOLVE         => CURL_IPRESOLVE_V4,
                    CURLOPT_TCP_KEEPALIVE     => 1,
                    CURLOPT_TCP_KEEPIDLE      => 15,
                    CURLOPT_TCP_KEEPINTVL     => 15,
                    CURLOPT_DNS_CACHE_TIMEOUT => 300,
                ],
                // при наличии прокси (HK/SG) — раскомментируйте:
                // 'proxy' => config('services.tourmind.proxy'), // например http://user:pass@host:port
                // 'verify' => true, // НЕ выключайте в проде
            ])
                ->acceptJson()
                ->asJson()
                ->timeout(120)
                ->connectTimeout(25)
                ->retry(
                    6,
                    fn($attempt) => 1000 * $attempt,
                    function ($e, $request) {
                        return $e instanceof ConnectionException
                            || optional($e->response)->serverError();
                    },
                    throw: false
                )
                ->withHeaders([
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection'      => 'keep-alive',
                ])
                ->post($url, $payload);

            if (!$resp->successful()) {
                \Log::warning('TM RoomStaticList HTTP fail', [
                    'status' => $resp->status(),
                    'body'   => mb_substr($resp->body(), 0, 1000),
                ]);
                return ['ok' => false, 'status' => $resp->status(), 'error' => $resp->body()];
            }
            return ['ok' => true, 'data' => $resp->json()];
        } catch (\Throwable $e) {
            \Log::warning('TM RoomStaticList timeout', ['msg' => $e->getMessage()]);
            return ['ok' => false, 'status' => 0, 'error' => $e->getMessage()];
        }
    }
}