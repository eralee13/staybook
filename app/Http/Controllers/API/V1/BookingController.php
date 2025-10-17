<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;

class BookingController extends Controller
{
    /**
     * ВЕРИФИКАЦИЯ: отправляем { "Booking": {...} } на /reservation/v1/bookings/verify,
     * вытаскиваем токен CreateBookingToken и отдаём партнёру.
     */
    public function verify(Request $request)
    {
        $booking = $this->buildBookingPayload($request, true); // midnight
        $json = json_encode(['Booking' => $booking], JSON_UNESCAPED_UNICODE);

        \Log::info('Exely verify (root Booking)', ['body' => $json]);

        $resp = \Http::timeout(60)
            ->withHeaders($this->exelyHeaders())
            ->withBody($json, 'application/json')
            ->post($this->exelyApiBase().'/reservation/v1/bookings/verify');

        if ($resp->successful()) {
            return response()->json($resp->json());
        }

        \Log::warning('Exely verify failed', ['status' => $resp->status(), 'body' => $resp->body()]);
        return response()->json([
            'error' => ['code' => 'exely_verify_failed', 'details' => $resp->json()],
        ], $resp->status());
    }

    /**
     * СОЗДАНИЕ: принимаем CreateBookingToken (строка) из шага verify,
     * отправляем { "Booking": {..., "CreateBookingToken": "<token>" } } на /bookings.
     */

    public function store(Request $request)
    {
        // 1) токен из verify
        $token = (string) $request->input('createBookingToken');
        if ($token === '') {
            return response()->json([
                'error' => ['code' => 'bad_request', 'message' => 'createBookingToken is required (from verify step)'],
            ], 422);
        }

        // 2) собираем booking ровно как на verify
        $booking = $this->buildBookingPayload($request);

        // валюта ДОЛЖНА совпадать с verify (в твоих логах verify=USD)
        if ($request->filled('verifyCurrencyCode')) {
            $booking['currencyCode'] = (string) $request->input('verifyCurrencyCode');
        }

        // 3) КЛЮЧЕВАЯ СТРОКА: токен кладём ВНУТРЬ booking
        $booking['createBookingToken'] = $token;

        // 4) тело — только {"booking":{...}}, БЕЗ хедера CreateBookingToken
        $json = json_encode(['booking' => $booking], JSON_UNESCAPED_UNICODE);
        Log::info('Exely booking final payload (body.booking.createBookingToken)', ['body' => $json]);

        $resp = Http::timeout(60)
            ->withHeaders($this->exelyHeaders()) // без CreateBookingToken
            ->withBody($json, 'application/json')
            ->post($this->exelyApiBase() . '/reservation/v1/bookings');

        if ($resp->successful()) {
            $data  = $resp->json();
            $ext   = $data['booking'] ?? $data['Booking'] ?? [];

            \App\Models\Book::create([
                'hotel_id'      => $request->get('propertyId'),
                'room_id'       => $request->get('roomTypeId'),
                'rate_id'       => $request->get('ratePlanId'),
                'title'         => $request->get('firstName', 'Guest'),
                'phone'         => $request->get('phone'),
                'email'         => $request->get('email'),
                'comment'       => $request->get('comment'),
                'adult'         => $request->get('adultCount'),
                'childages'     => implode(',', (array) $request->get('childAges', [])),
                'sum'           => $request->get('total', 0),
                'arrivalDate'   => $request->get('arrivalDate'),
                'departureDate' => $request->get('departureDate'),
                'status'        => 'Reserved',
                'book_token'    => $ext['number'] ?? ('EXELY-' . uniqid()),
                'user_id'       => Auth::id() ?? 99,
            ]);

            return response()->json(['message' => 'Booking created successfully', 'data' => $data], 201);
        }

        \Log::warning('Exely booking failed', ['status' => $resp->status(), 'body' => $resp->body()]);
        return response()->json([
            'error' => ['code' => 'exely_booking_failed', 'details' => $resp->json()],
        ], $resp->status());
    }

    /* ======================== helpers ======================== */

    private function normDateTimeMidnight($dtTime, $dt): ?string
    {
        // всегда YYYY-MM-DDT00:00
        if ($dtTime) {
            $date = substr($dtTime, 0, 10);
            return $date . 'T00:00';
        }
        if ($dt) {
            return $dt . 'T00:00';
        }
        return null;
    }

    private function fetchRoomStayMatch(
        string $propertyId,
        string $arrivalDateTime, // ISO 8601
        string $departureDateTime,
        int $adultCount,
        array $childAges,
        string $roomTypeId,
        string $ratePlanId,
        ?string $checksum
    ): ?array {
        $arrDate = substr($arrivalDateTime, 0, 10);
        $depDate = substr($departureDateTime, 0, 10);

        $url = rtrim($this->exelyApiBase(), '/')
            . '/search/v1/properties/' . urlencode($propertyId)
            . '/room-stays?arrivalDate=' . urlencode($arrDate)
            . '&departureDate=' . urlencode($depDate)
            . '&adults=' . $adultCount
            . '&includeExtraStays=false&includeExtraServices=false';

        foreach ($childAges as $age) {
            $url .= '&childAges=' . (int)$age;
        }

        $resp = Http::timeout(60)->withHeaders($this->exelyHeaders())->get($url);
        if (!$resp->successful()) {
            Log::warning('Exely fetch room-stays failed', [
                'status' => $resp->status(), 'url' => $url, 'body' => $resp->body()
            ]);
            return null;
        }

        $items = $resp->json('roomStays') ?? [];
        foreach ($items as $it) {
            $rtId = (string)($it['roomType']['id'] ?? '');
            $rpId = (string)($it['ratePlan']['id'] ?? '');
            $cs   = (string)($it['checksum'] ?? '');

            $checksumOk = $checksum ? ($cs === $checksum) : true;

            if ($rtId === $roomTypeId && $rpId === $ratePlanId && $checksumOk) {
                $placements = $it['roomType']['placements'] ?? null;
                $stayDates  = $it['stayDates'] ?? null;

                // нормализуем placements
                $outPl = [];
                if (is_array($placements)) {
                    foreach ($placements as $p) {
                        if (isset($p['code'], $p['count'])) {
                            $outPl[] = [
                                'code'  => (string)$p['code'],
                                'count' => (int)$p['count'],
                                'kind'  => $p['kind'] ?? 'Adult',
                            ];
                        }
                    }
                }

                // нормализуем stayDates
                $arrDT = $stayDates['arrivalDateTime']   ?? null;
                $depDT = $stayDates['departureDateTime'] ?? null;

                return [
                    'placements' => $outPl ?: null,
                    'arrivalDateTime'   => $arrDT,
                    'departureDateTime' => $depDT,
                ];
            }
        }

        return null;
    }

    private function buildBookingPayload(Request $request): array
    {
        $adultCount = (int) $request->input('adultCount', 1);
        $childAges  = $this->parseChildAges($request->childAges);

        $roomTypeId = (string) $request->roomTypeId;
        $ratePlanId = (string) $request->ratePlanId;
        $checksum   = $request->filled('checkSum') ? (string)$request->checkSum : null;
        $propertyId = (string) $request->propertyId;

        // Первичная заготовка дат (на случай если не найдём в room-stays точные времена)
        $arrDT = $this->normDateTime($request->arrivalDateTime, $request->arrivalDate, '14:00');
        $depDT = $this->normDateTime($request->departureDateTime, $request->departureDate, '12:00');

        // 1) Что прислал партнёр
        $placements = $this->cleanPlacements($request->placements, $adultCount);

        // 2) Если не прислал — тянем из Exely и ЗАБИРАЕМ ТАМ ЖЕ точные stayDates
        $match = null;
        if (($placements === null || $placements === []) && $propertyId && $roomTypeId && $ratePlanId && $arrDT && $depDT) {
            $match = $this->fetchRoomStayMatch(
                $propertyId, $arrDT, $depDT, $adultCount, $childAges, $roomTypeId, $ratePlanId, $checksum
            );
            if ($match) {
                if (!empty($match['placements'])) {
                    $placements = $match['placements'];
                }
                if (!empty($match['arrivalDateTime'])) {
                    $arrDT = $match['arrivalDateTime'];
                }
                if (!empty($match['departureDateTime'])) {
                    $depDT = $match['departureDateTime'];
                }
            }
            \Log::info('Room-stay match', [
                'placementsGot' => is_array($placements) ? count($placements) : 0,
                'arrDT' => $arrDT,
                'depDT' => $depDT,
            ]);
        }

        // 3) Если всё ещё нет placements — валидируемся сами (чтобы не ловить 400 от Exely)
        if ($placements === null || $placements === []) {
            abort(response()->json([
                'error' => [
                    'code'    => 'validation_error',
                    'message' => 'placements required: передайте placements или выберите связку из /room-stays (мы тогда подтянем автоматически)',
                ]
            ], 422));
        }

        // Собираем payload
        return [
            'propertyId' => $propertyId,
            'roomStays'  => [[
                'stayDates' => [
                    'arrivalDateTime'   => $arrDT, // ТЕ ЖЕ ЧТО В room-stays
                    'departureDateTime' => $depDT, // ТЕ ЖЕ ЧТО В room-stays
                ],
                'ratePlan'   => ['id' => $ratePlanId],
                'roomType'   => [
                    'id'         => $roomTypeId,
                    'placements' => $placements,
                ],
                'guestCount' => [
                    'adultCount' => $adultCount,
                    'childAges'  => $childAges,
                ],
                'guests' => [[
                    'firstName'   => (string) $request->input('firstName', 'Guest'),
                    'lastName'    => (string) $request->input('lastName', 'Guest'),
                    'middleName'  => (string) $request->input('firstName', 'Guest'),
                    'citizenship' => 'KGS',
                    'sex'         => $request->input('sex', 'Male'),
                ]],
                'checksum' => (string) ($checksum ?? ''),
                'services' => [],
            ]],
            'services'        => [],
            'customer'        => [
                'firstName'   => (string) $request->input('firstName', 'Guest'),
                'lastName'    => (string) $request->input('lastName', 'Guest'),
                'middleName'  => (string) $request->input('firstName', 'Guest'),
                'citizenship' => 'KGS',
                'contacts'    => [
                    'phones' => [[ 'phoneNumber'  => (string) $request->input('phone', '+996') ]],
                    'emails' => [[ 'emailAddress' => (string) $request->input('email', 'guest@example.com') ]],
                ],
                'comment' => (string) $request->input('comment', ''),
            ],
            'prepayment'      => [
                'remark'      => 'Payment in channel',
                'paymentType' => 'Cash',
                'prepaidSum'  => 0,
            ],
            'bookingComments' => array_filter([(string) $request->input('comment', '')]),
            'currencyCode'    => (string) $request->input('currencyCode', 'USD'),
            // createBookingToken добавляйте ТОЛЬКО в /bookings (store), verify его не требует
        ];
    }

    private function parseChildAges($value): array
    {
        if (is_array($value)) return array_map('intval', $value);
        if (is_string($value)) return array_map('intval', array_filter(explode(',', $value)));
        return [];
    }

    private function normDateTime($dtTime, $dt, $defaultTime): ?string
    {
        if ($dtTime) return str_contains($dtTime, 'T') ? $dtTime : ($dtTime . 'T' . $defaultTime);
        if ($dt)     return $dt . 'T' . $defaultTime;
        return null;
    }

    private function cleanPlacements($value, int $adultCount): ?array
    {
        $arr = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : null);
        if (!is_array($arr)) return null;

        $ok = [];
        foreach ($arr as $i) {
            if (is_array($i) && isset($i['code'], $i['count'])) {
                $ok[] = [
                    'code'  => (string) $i['code'],
                    'count' => (int) $i['count'],
                    'kind'  => isset($i['kind']) ? (string) $i['kind'] : 'Adult',
                ];
            }
        }
        return $ok ?: null;
    }

    private function fetchPlacementsFromExely(
        string $propertyId,
        string $arrivalDateTime, // ISO 8601 c 'T'
        string $departureDateTime,
        int $adultCount,
        array $childAges,
        string $roomTypeId,
        string $ratePlanId,
        ?string $checksum
    ): ?array {
        $arrDate = substr($arrivalDateTime, 0, 10);
        $depDate = substr($departureDateTime, 0, 10);

        $url = rtrim($this->exelyApiBase(), '/')
            . '/search/v1/properties/' . urlencode($propertyId)
            . '/room-stays?arrivalDate=' . urlencode($arrDate)
            . '&departureDate=' . urlencode($depDate)
            . '&adults=' . $adultCount
            . '&includeExtraStays=false&includeExtraServices=false';

        foreach ($childAges as $age) {
            $url .= '&childAges=' . (int) $age;
        }

        $resp = \Http::timeout(60)->withHeaders($this->exelyHeaders())->get($url);
        if (!$resp->successful()) {
            \Log::warning('Exely fetch placements failed', [
                'status'=>$resp->status(), 'url'=>$url, 'body'=>$resp->body()
            ]);
            return null;
        }

        $items = $resp->json('roomStays') ?? [];
        foreach ($items as $it) {
            $rtId = (string)($it['roomType']['id'] ?? '');
            $rpId = (string)($it['ratePlan']['id'] ?? '');
            $cs   = (string)($it['checksum'] ?? '');

            if ($rtId === $roomTypeId && $rpId === $ratePlanId && ($checksum ? $cs === $checksum : true)) {
                $placements = $it['roomType']['placements'] ?? null;
                if (is_array($placements) && $placements) {
                    // нормализуем
                    $out = [];
                    foreach ($placements as $p) {
                        if (isset($p['code'], $p['count'])) {
                            $out[] = [
                                'code'  => (string)$p['code'],
                                'count' => (int)$p['count'],
                                'kind'  => $p['kind'] ?? 'Adult',
                            ];
                        }
                    }
                    return $out ?: null;
                }
            }
        }
        return null;
    }

    private function exelyApiBase(): string
    {
        return rtrim(config('services.exely.base') ?? config('services.exely')['base'] ?? '', '/');
    }

    private function exelyHeaders(): array
    {
        return [
            'x-api-key'    => config('services.exely.key'),
            'accept'       => 'application/json',
            'Content-Type' => 'application/json; charset=utf-8',
        ];
    }
}