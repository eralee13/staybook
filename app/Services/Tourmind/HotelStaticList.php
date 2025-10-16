<?php
namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Hotel;
use App\Models\Amenity;
use App\Models\Room;
use App\Models\Image;
use Throwable;
class HotelStaticList
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    protected string $tm_agent_code;
    protected string $tm_user_name;
    protected string $tm_password;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService   = $tmApiService;
        $this->baseUrl        = rtrim((string) config('app.tm_base_url'), '/');
        $this->tm_agent_code  = (string) config('app.tm_agent_code');
        $this->tm_user_name   = (string) config('app.tm_user_name');
        $this->tm_password    = (string) config('app.tm_password');
    }

    /**
     * Импорт статик-отелей для одной страны.
     *
     * @param  string $countryCode ISO-код страны (напр. 'UA')
     * @param  int    $pageSize    1..500
     * @param  int    $maxPages    0 = без лимита, иначе обрежем
     * @return array  ['ok'=>bool, 'imported'=>int, 'pages'=>int, 'errors'=>int]
     */
    public function getHotelList(string $countryCode, int $pageSize = 200, int $maxPages = 0): array
    {
        if ($this->baseUrl === '') {
            return ['ok' => false, 'imported' => 0, 'pages' => 0, 'errors' => 1, 'err' => 'tm_base_url пуст'];
        }

        $pageIndex   = 1;
        $imported    = 0;
        $errors      = 0;
        $seenHotelIds = [];

        do {
            $payload = [
                'CountryCode'   => strtoupper(trim($countryCode)),
                'Pagination'    => [
                    'PageIndex' => $pageIndex,
                    'PageSize'  => max(1, min($pageSize, 500)),
                ],
                'RequestHeader' => [
                    'AgentCode'   => $this->tm_agent_code,
                    'Password'    => $this->tm_password,
                    'UserName'    => $this->tm_user_name,
                    'RequestTime' => now()->format('Y-m-d H:i:s'),
                ],
            ];

            try {
                $resp = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ])
                    ->connectTimeout(15)
                    ->timeout(90)
                    ->retry(4, 1000, function ($exception, $request) {
                        // Ретрай только при сетевых таймаутах/5xx/429
                        if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                            return true;
                        }
                        $response = method_exists($exception, 'response') ? $exception->response : null;
                        return $response && ($response->serverError() || $response->status() === 429);
                    }, throw: false)
                    ->post("{$this->baseUrl}/HotelStaticList", $payload);

                if (!$resp->successful()) {
                    $errors++;
                    Log::channel('tourmind')->warning('HotelStaticList non-200', [
                        'status' => $resp->status(),
                        'body'   => $resp->body(),
                        'page'   => $pageIndex,
                        'country'=> $countryCode,
                    ]);
                    // если неуспех — прекращаем цикл, чтобы не крутить пустую пагинацию
                    break;
                }

                $json      = $resp->json() ?? [];
                $result    = $json['HotelStaticListResult'] ?? [];
                $hotels    = $result['Hotels'] ?? [];
                $pageCount = (int) ($result['Pagination']['PageCount'] ?? 0);

                if (empty($hotels)) {
                    // пустая страница — выходим
                    break;
                }

                foreach ($hotels as $h) {
                    try {
                        $tmHotelId = $h['HotelId'] ?? null;
                        if (!$tmHotelId) {
                            continue;
                        }
                        // защита от дублей в рамках одного запуска
                        if (isset($seenHotelIds[$tmHotelId])) {
                            continue;
                        }
                        $seenHotelIds[$tmHotelId] = true;

                        $name    = (string) ($h['Name'] ?? '');
                        $code    = Str::slug($name) ?: ('hotel-' . $tmHotelId);

                        // Телефон
                        $phone = '';
                        if (!empty($h['Phone'])) {
                            $phone = preg_replace('/[^+\d]/', '', (string) $h['Phone']);
                            if ($phone !== '' && !Str::startsWith($phone, '+')) {
                                $phone = '+' . $phone;
                            }
                        }

                        // Удобства
                        $amenitiesHotel = collect($h['AmenitiesHotel'] ?? [])
                            ->pluck('name')->filter()->unique()->implode(', ');
                        $amenitiesRoom = collect($h['AmenitiesRoom'] ?? [])
                            ->pluck('name')->filter()->unique()->implode(', ');

                        // Картинки (берём до 3 уникальных ссылок 1000px)
                        $images = collect($h['Images'] ?? [])
                            ->map(fn ($it) => [
                                'href'     => data_get($it, 'links.1000px.href'),
                                'category' => data_get($it, 'category'),
                            ])
                            ->filter(fn ($it) => !empty($it['href']))
                            ->unique('href')
                            ->take(3)
                            ->values()
                            ->all();

                        // Вставляем/обновляем отель
                        $hotel = Hotel::updateOrCreate(
                            ['tourmind_id' => $tmHotelId],
                            [
                                'code'          => strtolower($code),
                                'title'         => $name,
                                'title_en'      => $name,
                                'rating'        => (int) ($h['StarRating'] ?? 0),
                                'address_en'    => (string) ($h['Address'] ?? ''),
                                'country_code'  => (string) ($h['CountryCode'] ?? ''),
                                'city'          => (string) ($h['CityName'] ?? ''),
                                'utc'           => '', // TODO: заполните своей логикой, если нужно
                                'lat'           => (string) ($h['Latitude'] ?? ''),
                                'lng'           => (string) ($h['Longitude'] ?? ''),
                                'phone'         => $phone,
                                'description_en'=> (string) data_get($h, 'Description.Location', ''),
                                'image'         => '', // превью кладём в images таблицу ниже
                                'status'        => 1,
                                'apiName' => 'tm'
                            ]
                        );

                        // Удобства отеля
                        if ($amenitiesHotel !== '') {
                            Amenity::updateOrCreate(
                                ['hotel_id' => $hotel->id],
                                ['services' => $amenitiesHotel]
                            );
                        }

                        // Создаём «комнатный» контейнер под статику (если у вас отдельные типы — замените логику)
                        Room::updateOrCreate(
                            ['hotel_id' => $hotel->id], // ключ — 1 запись per hotel
                            [
                                'title'          => '',
                                'amenities'      => $amenitiesRoom,
                                'description_en' => (string) data_get($h, 'Description.Rooms', null),
                            ]
                        );

                        // Сохраняем изображения
                        foreach ($images as $i => $im) {
                            try {
                                $localPath = $this->tmApiService->saveHotelImage($im['href'], $hotel->id);
                                Image::updateOrCreate(
                                    [
                                        'hotel_id' => $hotel->id,
                                        'category' => (string) ($im['category'] ?? 'photo'),
                                        'caption'  => $i === 0 ? 'Primary' : 'Gallery',
                                    ],
                                    [
                                        'image' => $localPath ?? '',
                                    ]
                                );
                            } catch (Throwable $e) {
                                Log::channel('tourmind')->warning('saveHotelImage failed', [
                                    'hotel_id' => $hotel->id,
                                    'href'     => $im['href'],
                                    'err'      => $e->getMessage(),
                                ]);
                            }
                        }
                        $imported++;
                    } catch (Throwable $e) {
                        $errors++;
                        Log::channel('tourmind')->error('HotelStaticList item failed', [
                            'err'   => $e->getMessage(),
                            'hotel' => $h ?? null,
                        ]);
                        // продолжаем остальные отели
                    }
                }

                $pageIndex++;

                if ($maxPages > 0 && $pageIndex > $maxPages) {
                    break;
                }
            } catch (Throwable $e) {
                $errors++;
                Log::channel('tourmind')->error('Hotel Static List - request failed', [
                    'err'     => $e->getMessage(),
                    'country' => $countryCode,
                    'page'    => $pageIndex,
                ]);
                // при сетевых фейлах можно сделать маленькую паузу
                usleep(300_000); // 300ms
                // и перейти к следующей попытке/странице или прервать:
                break;
            }
        } while (true);

        return [
            'ok'       => $imported > 0 && $errors === 0,
            'imported' => $imported,
            'pages'    => $pageIndex - 1,
            'errors'   => $errors,
        ];
    }

    /**
     * Обход по всем странам из сервиса.
     */
    public function getHotelListForAllCountries(): array
    {
        $codes = $this->tmApiService->getCountryCodes(); // верните массив ISO-кодов
        $summary = ['ok' => true, 'countries' => []];

        foreach ($codes as $cc) {
            $res = $this->getHotelList((string) $cc);
            $summary['countries'][$cc] = $res;
            if (!$res['ok']) {
                $summary['ok'] = false;
            }
        }

        return $summary;
    }
}