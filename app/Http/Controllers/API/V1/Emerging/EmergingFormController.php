<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Book;
use App\Models\Room;
use App\Models\Rate;
use App\Models\CancellationRule;

class EmergingFormController extends Controller
{
    public $keyId, $apiKey, $url, $coef, $language;
    public $hotelDetail, $hotelLocalData;
    public $guestsall, $childs_name;

    public function __construct()
    {
        $this->keyId    = (string) config('app.emerging_key_id');
        $this->apiKey   = (string) config('app.emerging_api_key');
        $this->url      = 'https://api.worldota.net/api/b2b/v3';
        $this->coef     = config('app.main_coef');
        $this->language = app()->getLocale();
    }

    private function etgClient()
    {
        $auth = base64_encode($this->keyId . ':' . $this->apiKey);

        $opts = [];
        if (app()->isLocal()) {
            $opts['debug'] = fopen(storage_path('logs/laravel.log'), 'a');
        }

        return Http::timeout(30)
            ->acceptJson()
            ->withoutRedirecting()
            ->withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type'  => 'application/json',
            ])
            ->withOptions($opts);
    }

    private function normalizeGuests($rooms): array
    {
        if (!is_iterable($rooms)) {
            return [['adults' => 1, 'children' => []]];
        }

        $out = [];
        foreach ($rooms as $r) {
            $adults = (int) ((is_array($r) && array_key_exists('adults', $r)) ? $r['adults'] : 1);
            if ($adults <= 0) $adults = 1;

            $children = [];
            if (is_array($r) && array_key_exists('childAges', $r) && is_iterable($r['childAges'])) {
                foreach ($r['childAges'] as $age) {
                    if ($age === '' || $age === null) continue;
                    $age = (int) $age;
                    if ($age < 0)  $age = 0;
                    if ($age > 17) $age = 17;
                    $children[] = $age;
                }
            }

            $out[] = ['adults' => $adults, 'children' => $children];
        }

        if (empty($out)) {
            $out[] = ['adults' => 1, 'children' => []];
        }

        return $out;
    }


    public function emergingGetHotels(Request $request)
    {
        // local hotels
        $expl  = explode('-', (string) $request->city);
        $city  = $expl[1] ?? null;

        $query = Hotel::whereNotNull('emerging_id');
        if ($city) {
            $query->where('city', $city);
        }
        if ($request->rating) {
            $query->where('rating', (int) $request->rating);
        }

        $query->with(['images', 'amenity']);

        $this->hotelLocalData = $query->get()
            ->mapWithKeys(fn($hotel) => [$hotel->emerging_id => $hotel])
            ->toArray();

        // supplier hotels by region
        $result = (array) $this->searchHotelsByCityOrId($request);

        if (!empty($result['data']['hotels']) && is_array($result['data']['hotels'])) {
            foreach ($result['data']['hotels'] as &$hotele) {
                $hid = (int) ($hotele['hid'] ?? 0);
                $hotele['localData'] = $hid ? ($this->hotelLocalData[$hid] ?? null) : null;

                // --- нормализуем минимальную цену ---
                $minAmount =
                    data_get($hotele, 'min_price.amount') ??
                    data_get($hotele, 'price_from.amount') ??
                    data_get($hotele, 'best_offer.total') ??
                    data_get($hotele, 'best_offer.total.amount') ??   // на случай другой структуры
                    data_get($hotele, 'min_total') ??
                    data_get($hotele, 'min_total.amount') ??
                    data_get($hotele, 'total') ??
                    data_get($hotele, 'total.amount');

                $minCurr =
                    data_get($hotele, 'min_price.currency') ??
                    data_get($hotele, 'price_from.currency') ??
                    data_get($hotele, 'best_offer.currency') ??
                    data_get($hotele, 'best_offer.total.currency') ??
                    data_get($hotele, 'currency') ??
                    'USD';

                // если нужна конверсия/коэф — делай её здесь
                $convTotal = (is_numeric($minAmount) && $minAmount > 0) ? (float) $minAmount : null;

                // (опц.) превратить код валюты в символ; иначе на фронте увидишь "USD"
                $symbolMap = ['USD'=>'$', 'EUR'=>'€', 'GBP'=>'£', 'KGS'=>'с', 'RUB'=>'₽'];
                $convSymbol = $symbolMap[$minCurr] ?? $minCurr; // оставляем код, если символ неизвестен

                if ($convTotal !== null) {
                    $hotele['price']       = $convTotal;
                    $hotele['conv_total']  = $convTotal;
                    $hotele['conv_symbol'] = $convSymbol;
                } else {
                    $hotele['price']       = null;
                    $hotele['conv_total']  = null;
                    $hotele['conv_symbol'] = $convSymbol;
                    // (опц.) можно включить отладку, если нужно понять, почему нет цены:
                    // Log::debug('SERP no min price for hotel', ['hid'=>$hid, 'hotel'=>data_get($hotele,'title')]);
                }
            }
            unset($hotele);
        }

        return $result;
    }

    public function searchHotelsByCityOrId(Request $request)
    {
        $rooms   = (array) $request->input('rooms', [['adults' => 1, 'childAges' => []]]);
        $guests  = $this->normalizeGuests($rooms);
        $currency = session('currency', 'USD');

        $payload = [
            'checkin'   => (string) $request->arrivalDate,
            'checkout'  => (string) $request->departureDate,
            'residency' => strtoupper((string) $request->input('residency', 'KG')),
            'language'  => $this->language,
            'guests'    => $guests,
            'timeout'   => 30,
            'region_id' => (int) $request->region_id,
            'currency'  => $currency,
        ];

        if (app()->isLocal()) {
            Log::channel('emerging')->info('Search /search/serp/region/ - Payload ', $payload);
        }

        $response = $this->etgClient()->post($this->url . '/search/serp/region/', $payload);
        return $response->json();
    }

    private function resolveHid(Request $request, int $routeHid): int
    {
        $qHid = (int) $request->query('apiHotelId', 0);
        if ($qHid > 0) return $qHid;

        if ($routeHid > 0) return $routeHid;

        $localHotelId = (int) $request->query('hid', 0);
        if ($localHotelId > 0) {
            $ehid = (int) Hotel::where('id', $localHotelId)->value('emerging_id');
            return $ehid > 0 ? $ehid : 0;
        }
        return 0;
    }

    private function diagSerpThenHp(array $rooms, string $checkin, string $checkout, string $residency, int $regionId = 0): void
    {
        if (!app()->isLocal()) return;

        $currency = session('currency', 'USD');

        $serpPayload = [
            'checkin'   => $checkin,
            'checkout'  => $checkout,
            'residency' => $residency,
            'language'  => app()->getLocale() ?: 'ru',
            'currency'  => $currency,
            'timeout'   => 30,
            'region_id' => $regionId,
            'guests'    => $this->normalizeGuests($rooms),
        ];
        $serp   = $this->etgClient()->post($this->url . '/search/serp/region/', $serpPayload);
        $json   = $serp->json();
        $hotels = (array) data_get($json, 'data.hotels', []);
        Log::debug('ETG DIAG SERP', [
            'status'       => $serp->status(),
            'hotels_found' => count($hotels),
            'first_hid'    => (int) data_get($hotels, '0.hid', 0),
            'region_id'    => $regionId,
        ]);

        $firstHid = (int) data_get($hotels, '0.hid', 0);
        if ($firstHid > 0) {
            $hp = $this->etgClient()->post($this->url . '/search/hp', [
                'hid'       => $firstHid,
                'checkin'   => $checkin,
                'checkout'  => $checkout,
                'residency' => $residency,
                'language'  => app()->getLocale() ?: 'ru',
                'currency'  => $currency,
                'timeout'   => 30,
                'guests'    => $this->normalizeGuests($rooms),
            ]);
            $hpJson = $hp->json();
            Log::debug('ETG DIAG HP', [
                'status'     => $hp->status(),
                'hid'        => $firstHid,
                'rate_count' => data_get($hpJson, 'data.hotels.0.rates') ? count(data_get($hpJson, 'data.hotels.0.rates')) : 0,
            ]);
        }
    }

    public function searchRates(Request $request, $hid)
    {
        $hid  = (int) $this->resolveHid($request, (int)$hid);
        $ehid = (int) Hotel::where('emerging_id', $hid)->value('emerging_id');
        if ($ehid <= 0) {
            $try = (int) Hotel::where('id', $hid)->value('emerging_id');
            if ($try > 0) $hid = $try;
        }
        if ($hid <= 0) {
            Log::warning('ETG: HID unresolved', ['route_hid' => $hid, 'q' => $request->all()]);
            return ['rates' => [], 'hotel' => [], 'raw' => null, 'message' => 'Не удалось определить HID (emerging_id)'];
        }

        $checkin   = (string) $request->input('arrivalDate') ?: now()->toDateString();
        $checkout  = (string) $request->input('departureDate') ?: now()->addDay()->toDateString();
        $residency = strtoupper((string) $request->input('residency', 'KG'));
        $rooms     = (array) $request->input('rooms', [['adults' => 1, 'childAges' => []]]);
        $guests    = $this->normalizeGuests($rooms);
        $currency  = session('currency', 'USD');

        $payload = [
            'hid'       => $hid,
            'checkin'   => $checkin,
            'checkout'  => $checkout,
            'residency' => $residency,
            'language'  => app()->getLocale() ?: 'ru',
            'currency'  => $currency,
            'timeout'   => 30,
            'guests'    => $guests,
        ];

        if (app()->isLocal()) {
            Log::info('Hotel rates /search/hp/ - Payload ', $payload);
        }

        try {
            $resp = $this->etgClient()->post($this->url . '/search/hp', $payload);
            $json = $resp->json();

            if (app()->isLocal()) {
                Log::debug('ETG hp body sample', [
                    'hotel_count' => data_get($json, 'data.hotels') ? count(data_get($json, 'data.hotels')) : 0,
                    'rate_count'  => data_get($json, 'data.hotels.0.rates') ? count(data_get($json, 'data.hotels.0.rates')) : 0,
                    'error'       => data_get($json, 'error'),
                ]);
            }

            if (!$resp->successful()) {
                Log::warning('ETG hotel call failed', ['code' => $resp->status(), 'body' => $resp->body()]);
                return ['rates' => [], 'hotel' => [], 'raw' => $json, 'message' => 'Поставщик вернул ошибку: ' . $resp->status()];
            }

            $rates = (array) data_get($json, 'data.hotels.0.rates', []);
            $hotel = (array) data_get($json, 'data.hotels.0', []);
            $msg   = empty($rates) ? 'Тарифов не найдено для выбранных дат' : '';

            if (empty($rates)) {
                $this->diagSerpThenHp($rooms, $checkin, $checkout, $residency, (int) $request->input('region_id', 0));
            }

            return ['rates' => $rates, 'hotel' => $hotel, 'raw' => $json, 'message' => $msg];

        } catch (\Throwable $e) {
            Log::error('ETG exception', ['msg' => $e->getMessage()]);
            return ['rates' => [], 'hotel' => [], 'raw' => null, 'message' => 'Ошибка соединения с поставщиком'];
        }
    }

    public function preBook(Request $request)
    {
        $response = $this->etgClient()->post($this->url . '/hotel/prebook/', [
            'timeout'                 => 20,
            'hash'                    => $request->book_hash,
            'price_increase_percent'  => (int) ($request->increase_percent ?? 0),
        ]);
        return $response->json();
    }

    public function startProcess(Request $request)
    {
        $userId = Auth::id();
        $rooms  = (array) $request->input('rooms', []);
        $adults = 0;
        $allChildAges = [];
        $childs = 0;
        $roomCount = 0;
        $guestsArr = [];

        foreach ($rooms as $room) {
            $roomCount++;
            $adultsRoom = (int) ($room['adults'] ?? 0);
            $adults += $adultsRoom;

            $children = [];
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $age = (int) $age;
                    $children[] = $age;
                    $allChildAges[] = $age;
                    $childs++;
                }
            }

            $guestsArr[] = ['adults' => $adultsRoom, 'children' => $children];
        }

        $mappingMeals = $this->mappingMeals();

        $response = $this->etgClient()->post($this->url . '/hotel/order/booking/form/', [
            'partner_order_id' => $request->token,
            'book_hash'        => $request->book_hash,
            'language'         => $this->language,
            'user_ip'          => $request->ip(),
            'timeout'          => 30,
        ]);

        $res = json_decode($response->body());
        $existbook = Book::where('book_token', $request->token)->first();

        if (!$existbook && isset($res->data->item_id)) {
            $etoken   = $res->data->partner_order_id ?? $request->token;
            $curr     = 'USD';
            $amount   = null;
            $paystype = null;

            foreach ((array)($res->data->payment_types ?? []) as $paytype) {
                if (($paytype->currency_code ?? '') === 'USD') {
                    $amount   = $paytype->amount ?? null;
                    $curr     = $paytype->currency_code ?? 'USD';
                    $paystype = $paytype->type ?? null;
                }
            }

            $room = Room::where('title_en', $request->room_name)->first();
            if (empty($room)) {
                $room = Room::updateOrCreate(
                    ['title_en' => $request->room_name, 'hotel_id' => $request->hotel_id],
                    ['title' => $request->room_name, 'title_en' => $request->room_name]
                );
            }

            // ФИО гостей (исправлен off-by-one)
            for ($i = 0; $i < $adults; $i++) {
                $fname = trim((string) $request->input('paxfname' . $i));
                if ($fname !== '') $this->guestsall[] = $fname;
            }
            for ($i = 0; $i < $childs; $i++) {
                $fio = trim((string) $request->input('child_name' . $i));
                if ($fio !== '') $this->childs_name[] = $fio;
            }

            $guests      = implode(',', $this->guestsall ?? []);
            $childsName  = implode(',', $this->childs_name ?? []);
            $childAges   = implode(',', $allChildAges ?? []);
            $utcdatetime = Carbon::now($request->utc)->format('Y-m-d H:i:s');

            $cancelDate = $request->cancelDate
                ? Carbon::parse($request->cancelDate)->format('Y-m-d H:i:s')
                : null;

            // CancellationRule
            if ($request->refundable) {
                $rule = CancellationRule::create([
                    'title'                  => 'Бесплатная отмена до указанной даты',
                    'is_refundable'          => 1,
                    'free_cancellation_days' => 0,
                    'penalty_type'           => 'fixed',
                    'penalty_amount'         => $request->cancelPrice ?? 0,
                    'end_date'               => $cancelDate ?? $utcdatetime,
                    'description'            => '',
                    'hotel_id'               => $request->hotel_id,
                ]);
            } else {
                $rule = CancellationRule::create([
                    'title'                  => 'Безвозвратный тариф',
                    'is_refundable'          => 0,
                    'free_cancellation_days' => 0,
                    'penalty_type'           => 'fixed',
                    'penalty_amount'         => $request->sum ?? 0,
                    'end_date'               => null,
                    'description'            => '',
                    'hotel_id'               => $request->hotel_id,
                ]);
            }
            $ruleid = $rule->id ?? null;

            $totalPrice = number_format(($request->price / $this->coef), 2, '.', '');

            $rate = Rate::create([
                'hotel_id'             => $request->hotel_id,
                'room_id'              => $room->id,
                'title'                => $request->rate_name ?? '',
                'title_en'             => $request->rate_name ?? '',
                'desc_en'              => null,
                'bed_type'             => $request->bedTypeDesc ?? '',
                'meal_id'              => $mappingMeals[$request->meal_id] ?? '',
                'allotment'            => null,
                'adult'                => $adults ?: 1,
                'child'                => $childs ?: 0,
                'children_allowed'     => 0,
                'free_children_age'    => 0,
                'currency'             => $curr,
                'price'                => $request->price,
                'price2'               => null,
                'child_extra_fee'      => 0,
                'availability'         => 0,
                'total_price'          => round($totalPrice),
                'cancellation_rule_id' => $ruleid,
            ]);

            $book = Book::firstOrCreate(
                ['book_token' => $etoken],
                [
                    'title'          => $guests,
                    'child_name'     => $childsName,
                    'hotel_id'       => $request->hotel_id,
                    'room_id'        => $room->id ?? null,
                    'rate_id'        => $rate->id ?? null,
                    'phone'          => $request->phone,
                    'email'          => $request->email,
                    'comment'        => $request->comment,
                    'room_count'     => $roomCount,
                    'adult'          => $adults ?: 1,
                    'child'          => $childs,
                    'childages'      => $childAges,
                    'price'          => $request->price,
                    'source_sym'     => 'USD',
                    'sum'            => $totalPrice,
                    'utc'            => $request->utc,
                    'cancellation_id'=> $ruleid,
                    'cancel_penalty' => $request->cancelPrice,
                    'currency'       => $curr ?? 'USD',
                    'cancel_date'    => $cancelDate ?? $utcdatetime,
                    'arrivalDate'    => $request->arrivalDate,
                    'departureDate'  => $request->departureDate,
                    'status'         => 'Pending',
                    'untax'          => $request->tax_not_included ?? '',
                    'user_id'        => $userId,
                    'api_type'       => 'emerging',
                    'agent_ref'      => '',
                ]
            );

            if (!isset($book->id)) {
                return ['status' => 'error', 'error' => 'not_created_locally'];
            }
        }

        return $response->json();
    }

    public function bookingFinish(Request $request, $data)
    {
        $language  = app()->getLocale();
        $rooms     = (array) $request->input('rooms', []);
        $currency  = $data['curr'] ?? session('currency', 'USD');

        $roomsData = [];
        $childIndex = 0;
        $adultIndex = 0;

        foreach ($rooms as $room) {
            $roomGuests = [];

            $adultCount = (int) ($room['adults'] ?? 0);
            for ($i = 0; $i < $adultCount; $i++) {
                $fname = trim((string) $request->input('paxfname' . $adultIndex, ''));
                $parts = preg_split('/\s+/', $fname, 3);
                $lastName  = $parts[0] ?? '';
                $firstName = $parts[1] ?? '';
                $thirdName = $parts[2] ?? '';
                $roomGuests[] = ['first_name' => $firstName, 'last_name' => trim($lastName . ' ' . $thirdName)];
                $adultIndex++;
            }

            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $fname = trim((string) $request->input('child_name' . $childIndex, ''));
                    $parts = preg_split('/\s+/', $fname, 3);
                    $lastName  = $parts[0] ?? '';
                    $firstName = $parts[1] ?? '';
                    $thirdName = $parts[2] ?? '';
                    $roomGuests[] = [
                        'first_name' => $firstName,
                        'last_name'  => trim($lastName . ' ' . $thirdName),
                        'age'        => (int) $age,
                        'is_child'   => true,
                    ];
                    $childIndex++;
                }
            }

            $roomsData[] = ['guests' => $roomGuests];
        }

        $totalPrice = number_format(($request->price / $this->coef), 2, '.', '');

        $payload = [
            'timeout'  => 60,
            'user'     => [
                'email'   => 'itsupport@staybook.asia', // либо $request->email
                'comment' => $request->comment,
                'phone'   => $request->phone
            ],
            'partner'  => [
                'partner_order_id'   => $data['etoken'],
                'amount_sell_b2b2c'  => round($totalPrice),
            ],
            'language' => $language,
            'rooms'    => $roomsData,
            'payment_type' => [
                'type'          => $data['type'],
                'amount'        => $data['amount'],
                'currency_code' => $currency,
            ],
        ];

        if (app()->isLocal()) {
            Log::channel('emerging')->info('Order Finish - Payload ', $payload);
        }

        $response = $this->etgClient()->post($this->url . '/hotel/order/booking/finish/', $payload);
        return $response->json();
    }

    public function finishStatus(Request $request)
    {
        $response = $this->etgClient()->post($this->url . '/hotel/order/booking/finish/status/', [
            'partner_order_id' => $request->token,
            'timeout'          => 60,
        ]);
        return $response->json();
    }

    public function etg_cancel(Request $request)
    {
        $response = $this->etgClient()->post($this->url . '/hotel/order/cancel/', [
            'partner_order_id' => $request->number,
            'timeout'          => 30,
        ]);
        return json_decode($response->body());
    }

    public function getBookingStatus($token)
    {
        try {
            $response = $this->etgClient()->post($this->url . '/hotel/order/booking/finish/status/', [
                'partner_order_id' => $token,
            ]);
            return $response->json();
        } catch (\Throwable $th) {
            Log::channel('emerging')->warning('Crontab Get Statuses failed', ['error' => $th->getMessage()]);
            return ["Error" => "Cron Statuses SearchOrder Ошибка при запросе к API: " . $th->getMessage()];
        }
    }

    public function updateBookingStatuses()
    {
        $books = Book::where('api_type', 'emerging')
            ->where('status', 'Pending')
            ->get(['id', 'book_token']);

        foreach ($books as $book) {
            $status = $this->getBookingStatus($book->book_token);
            if (($status['status'] ?? null) === 'ok') {
                Book::where('book_token', $book->book_token)->update(['status' => 'Confirmed']);
                echo "Done {$book->id}\n";
            } else {
                $err = $status['error'] ?? 'unknown';
                echo "Error - {$err} {$book->id}\n";
            }
        }
    }

    public function mappingMeals()
    {
        return [
            'nomeal'              => 1,
            'room-only'           => 1,
            'some-meal'           => 1,
            'breakfast-for-1'     => 1,
            'breakfast-for-2'     => 1,

            'breakfast'           => 2,
            'buffet'              => 2,
            'american-breakfast'  => 2,
            'asian-breakfast'     => 2,
            'chinese-breakfast'   => 2,
            'continental-breakfast'=> 2,
            'english-breakfast'   => 2,
            'irish-breakfast'     => 2,
            'israeli-breakfast'   => 2,
            'japanese-breakfast'  => 2,
            'scandinavian-breakfast'=> 2,
            'scottish-breakfast'  => 2,

            'half-board'          => 3,
            'half-board-dinner'   => 3,
            'half-board-lunch'    => 3,

            'full-board'          => 4,
            'soft-all-inclusive'  => 4,

            'all-inclusive'       => 5,
            'super-all-inclusive' => 5,
            'ultra-all-inclusive' => 5,

            'lunch'               => 6,
            'dinner'              => 7,
        ];
    }

    public function mappingMealsGrouped()
    {
        return [
            1 => ['nomeal','room-only','some-meal','breakfast-for-1','breakfast-for-2'],
            2 => ['breakfast','buffet','american-breakfast','asian-breakfast','chinese-breakfast','continental-breakfast','english-breakfast','irish-breakfast','israeli-breakfast','japanese-breakfast','scandinavian-breakfast','scottish-breakfast'],
            3 => ['half-board','half-board-dinner','half-board-lunch'],
            4 => ['full-board','soft-all-inclusive'],
            5 => ['all-inclusive','super-all-inclusive','ultra-all-inclusive'],
            6 => ['lunch'],
            7 => ['dinner'],
        ];
    }
}