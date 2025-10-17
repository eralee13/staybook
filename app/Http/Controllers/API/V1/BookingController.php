<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Верификация бронирования
     */
    public function verify(Request $request)
    {
        $booking = $this->buildBookingPayload($request, true); // midnight
        $json = json_encode(['Booking' => $booking], JSON_UNESCAPED_UNICODE);

        \Log::info('Exely verify (root Booking)', ['body' => $json]);

        $resp = \Http::timeout(60)
            ->withHeaders($this->exelyHeaders())
            ->withBody($json, 'application/json')
            ->post($this->exelyApiBase() . '/reservation/v1/bookings/verify');

        if ($resp->successful()) {
            return response()->json($resp->json());
        }

        \Log::warning('Exely verify failed', ['status' => $resp->status(), 'body' => $resp->body()]);
        return response()->json([
            'error' => ['code' => 'exely_verify_failed', 'details' => $resp->json()],
        ], $resp->status());
    }

    /**
     * Создание бронирования
     */
    public function store(Request $request)
    {
        // === 1. Проверяем токен ===
        $token = (string)$request->input('createBookingToken');
        if ($token === '') {
            return response()->json([
                'error' => [
                    'code' => 'bad_request',
                    'message' => 'createBookingToken (из verify) обязателен',
                ]
            ], 422);
        }

        // === 2. Собираем payload ===
        $booking = $this->buildBookingPayload($request);

        // Уточняем валюту из verify (если указана)
        if ($request->filled('verifyCurrencyCode')) {
            $booking['currencyCode'] = (string)$request->input('verifyCurrencyCode');
        }

        // Добавляем токен из verify внутрь booking
        $booking['createBookingToken'] = $token;

        // === 3. Логируем JSON перед отправкой ===
        $json = json_encode(['booking' => $booking], JSON_UNESCAPED_UNICODE);
        Log::info('Exely booking final payload', [
            'body' => $json,
            'hotel_id' => $request->input('propertyId'),
            'roomTypeId' => $request->input('roomTypeId'),
            'ratePlanId' => $request->input('ratePlanId'),
            'guest' => $request->input('firstName'),
        ]);

        // === 4. Отправляем в Exely ===
        $resp = Http::timeout(60)
            ->withHeaders($this->exelyHeaders())
            ->withBody($json, 'application/json')
            ->post($this->exelyApiBase() . '/reservation/v1/bookings');

        // === 5. Обрабатываем ответ ===
        if ($resp->successful()) {
            $data = $resp->json();
            $ext = $data['booking'] ?? $data['Booking'] ?? [];

            // Сохраняем локально
            Book::create([
                'hotel_id' => $request->get('propertyId'),
                'room_id' => $request->get('roomTypeId'),
                'rate_id' => $request->get('ratePlanId'),
                'arrivalDate' => $request->get('arrivalDate'),
                'departureDate' => $request->get('departureDate'),
                'title' => $request->get('firstName', 'Guest'),
                'phone' => $request->get('phone'),
                'email' => $request->get('email'),
                'comment' => $request->get('comment'),
                'adult' => $request->get('adultCount', 1),
                'childages' => implode(',', (array)$request->get('childAges', [])),
                'sum' => $ext['total']['priceBeforeTax'] ?? $request->get('total', 0),
                'currency' => $ext['currencyCode'] ?? $booking['currencyCode'] ?? 'USD',
                'status' => 'Reserved',
                'book_token' => $ext['number'] ?? $ext['createBookingToken'] ?? ('EXELY-' . uniqid()),
                //'free_cancel_deadline_utc' => $ext['cancellationPolicy']['freeCancellationDeadlineUtc'] ?? null,
                'cancel_penalty' => $ext['cancellationPolicy']['penaltyAmount'] ?? null,
                'user_id' => Auth::id() ?? $request->get('user_id'),
            ]);

            return response()->json([
                'message' => 'Booking created successfully',
                'data' => $data,
            ], 201);
        }

        // === 6. Логируем ошибку ===
        Log::warning('Exely booking failed', [
            'status' => $resp->status(),
            'body' => $resp->body(),
        ]);

        return response()->json([
            'error' => [
                'code' => 'exely_booking_failed',
                'details' => $resp->json(),
            ],
        ], $resp->status());
    }



    private function fetchRoomStayMatch(
        string  $propertyId,
        string  $arrivalDateTime, // ISO 8601
        string  $departureDateTime,
        int     $adultCount,
        array   $childAges,
        string  $roomTypeId,
        string  $ratePlanId,
        ?string $checksum
    ): ?array
    {
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
            $cs = (string)($it['checksum'] ?? '');

            $checksumOk = $checksum ? ($cs === $checksum) : true;

            if ($rtId === $roomTypeId && $rpId === $ratePlanId && $checksumOk) {
                $placements = $it['roomType']['placements'] ?? null;
                $stayDates = $it['stayDates'] ?? null;

                // нормализуем placements
                $outPl = [];
                if (is_array($placements)) {
                    foreach ($placements as $p) {
                        if (isset($p['code'], $p['count'])) {
                            $outPl[] = [
                                'code' => (string)$p['code'],
                                'count' => (int)$p['count'],
                                'kind' => $p['kind'] ?? 'Adult',
                            ];
                        }
                    }
                }

                // нормализуем stayDates
                $arrDT = $stayDates['arrivalDateTime'] ?? null;
                $depDT = $stayDates['departureDateTime'] ?? null;

                return [
                    'placements' => $outPl ?: null,
                    'arrivalDateTime' => $arrDT,
                    'departureDateTime' => $depDT,
                ];
            }
        }

        return null;
    }

    private function buildBookingPayload(Request $request): array
    {
        $adultCount = (int)$request->input('adultCount', 1);
        $childAges = $this->parseChildAges($request->childAges);

        $roomTypeId = (string)$request->roomTypeId;
        $ratePlanId = (string)$request->ratePlanId;
        $checksum = $request->filled('checkSum') ? (string)$request->checkSum : null;
        $propertyId = (string)$request->propertyId;

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
            Log::info('Room-stay match', [
                'placementsGot' => is_array($placements) ? count($placements) : 0,
                'arrDT' => $arrDT,
                'depDT' => $depDT,
            ]);
        }

        // 3) Если всё ещё нет placements — валидируемся сами (чтобы не ловить 400 от Exely)
        if ($placements === null || $placements === []) {
            abort(response()->json([
                'error' => [
                    'code' => 'validation_error',
                    'message' => 'placements required: передайте placements или выберите связку из /room-stays (мы тогда подтянем автоматически)',
                ]
            ], 422));
        }

        // Собираем payload
        return [
            'propertyId' => $propertyId,
            'roomStays' => [[
                'stayDates' => [
                    'arrivalDateTime' => $arrDT, // ТЕ ЖЕ ЧТО В room-stays
                    'departureDateTime' => $depDT, // ТЕ ЖЕ ЧТО В room-stays
                ],
                'ratePlan' => ['id' => $ratePlanId],
                'roomType' => [
                    'id' => $roomTypeId,
                    'placements' => $placements,
                ],
                'guestCount' => [
                    'adultCount' => $adultCount,
                    'childAges' => $childAges,
                ],
                'guests' => [[
                    'firstName' => (string)$request->input('firstName', 'Guest'),
                    'lastName' => (string)$request->input('lastName', 'Guest'),
                    'middleName' => (string)$request->input('firstName', 'Guest'),
                    'citizenship' => 'KGS',
                    'sex' => $request->input('sex', 'Male'),
                ]],
                'checksum' => (string)($checksum ?? ''),
                'services' => [],
            ]],
            'services' => [],
            'customer' => [
                'firstName' => (string)$request->input('firstName', 'Guest'),
                'lastName' => (string)$request->input('lastName', 'Guest'),
                'middleName' => (string)$request->input('firstName', 'Guest'),
                'citizenship' => 'KGS',
                'contacts' => [
                    'phones' => [['phoneNumber' => (string)$request->input('phone', '+996')]],
                    'emails' => [['emailAddress' => (string)$request->input('email', 'guest@example.com')]],
                ],
                'comment' => (string)$request->input('comment', ''),
            ],
            'prepayment' => [
                'remark' => 'Payment in channel',
                'paymentType' => 'Cash',
                'prepaidSum' => 0,
            ],
            'bookingComments' => array_filter([(string)$request->input('comment', '')]),
            'currencyCode' => (string)$request->input('currencyCode', 'USD'),
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
        if ($dt) return $dt . 'T' . $defaultTime;
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
                    'code' => (string)$i['code'],
                    'count' => (int)$i['count'],
                    'kind' => isset($i['kind']) ? (string)$i['kind'] : 'Adult',
                ];
            }
        }
        return $ok ?: null;
    }



    private function exelyApiBase(): string
    {
        return rtrim(config('services.exely.base') ?? config('services.exely')['base'] ?? '', '/');
    }

    private function exelyHeaders(): array
    {
        return [
            'x-api-key' => config('services.exely.key'),
            'accept' => 'application/json',
            'Content-Type' => 'application/json; charset=utf-8',
        ];
    }


    /**
     * Просмотр бронирования
     */
    public function show(Request $request, $id)
    {
        // базовый запрос с подгрузкой связей
        $query = Book::query()
            ->with(['hotel:id,title,city', 'room:id,title'])
            ->orderByDesc('created_at');

        // фильтр по hotel_id, если задан
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }

        // получаем конкретную бронь
        $booking = $query->where('id', $id)->first();

        // если не найдено → 404
        if (!$booking) {
            return response()->json([
                'error' => [
                    'code'    => 'not_found',
                    'message' => 'Booking not found',
                ],
            ], 404);
        }

        // нормализуем вывод
        $data = [
            'id'            => $booking->id,
            'hotel_id' => $booking->hotel_id,
            'room_id' => $booking->room_id,
            'rate'          => $booking->rate_id,
            'arrivalDate'   => $booking->arrivalDate,
            'departureDate' => $booking->departureDate,
            'adult'         => $booking->adult,
            'childages'     => $booking->childages,
            'currency'      => $booking->currency,
            'sum'           => $booking->sum,
            'phone'         => $booking->phone,
            'email'         => $booking->email,
            'user_id'       => $booking->user_id,
            'status'        => $booking->status,
            'book_token'    => $booking->book_token,
            'created_at'    => $booking->created_at->format('Y-m-d H:i:s'),
        ];

        return response()->json(['data' => $data], 200);
    }


    /**
     * Подсчет отмены бронирования
     */
    public function cancelCalculate(Request $request)
    {
        $numberRaw = (string) $request->input('book_token', $request->input('bookingNumber'));
        $cancelInp = $request->input('cancelTime', 'now');

        if (!$numberRaw) {
            return response()->json(['error' => [
                'code' => 'bad_request',
                'message' => 'Booking number (book_token) is required'
            ]], 400);
        }

        // Нормализуем и готовим время
        $number = trim(preg_replace('/[^\d\-]/', '', $numberRaw));
        $cancelUtc = \Carbon\Carbon::parse($cancelInp)->utc()->format('Y-m-d\TH:i:s\Z');

        $base = rtrim(config('services.exely.base'), '/');
        $url  = "{$base}/reservation/v1/bookings/{$number}/calculate-cancellation-penalty"
            . "?cancellationDateTimeUtc=" . urlencode($cancelUtc);

        Log::info('Exely cancel-calc request', ['url' => $url]);

        $resp = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept'    => 'text/plain',
            ])
            ->get($url);

        if ($resp->successful()) {
            // В Exely это plain/text, но содержимое — JSON
            $json = json_decode($resp->body(), true);
            return response()->json($json, 200);
        }

        Log::warning('Exely cancel-calc failed', [
            'status' => $resp->status(),
            'url'    => $url,
            'body'   => $resp->body(),
        ]);

        return response()->json([
            'error' => [
                'code' => 'exely_cancel_calc_failed',
                'message' => 'Provider error',
                'status' => $resp->status(),
                'body'   => $resp->body(),
            ]
        ], $resp->status());
    }

    /**
     * Подтверждение отмены бронирования
     */
    public function cancelConfirm(Request $request)
    {
        $v = Validator::make($request->all(), [
            'bookingNumber' => 'nullable|string',
            'expectedPenaltyAmount' => 'required|numeric',
        ]);
        if ($v->fails()) {
            return response()->json(['error' => ['code'=>'validation','details'=>$v->errors()]], 422);
        }

        // 1) Номер
        $exelyNumber = $request->bookingNumber;
        $book = null;
        if (!$exelyNumber) {
            if ($request->filled('book_id')) {
                $book = Book::find($request->integer('book_id'));
            } elseif ($request->filled('book_token')) {
                $book = Book::where('book_token', $request->string('book_token'))->first();
            }
            if ($book) $exelyNumber = $book->book_token;
        }
        if (!$exelyNumber) {
            return response()->json(['error'=>['code'=>'bad_request','message'=>'Не найден номер брони']], 400);
        }

        // 2) Запрос в Exely
        $base = rtrim(config('services.exely.base'), '/');
        $url  = "{$base}/reservation/v1/bookings/{$exelyNumber}/cancel";

        $payload = [
            'reason' => $request->input('reason', 'Booking cancellation'),
            'expectedPenaltyAmount' => (float) $request->input('expectedPenaltyAmount'),
        ];

        $resp = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => config('services.exely.key'),
                'accept'    => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

        if ($resp->successful()) {
            // обновим локально
            if ($book) {
                $book->status = 'Cancelled';
                $book->save();
            } else {
                Book::where('book_token', $exelyNumber)->update(['status' => 'Cancelled']);
            }
            return response()->json($resp->json(), 200);
        }

        Log::warning('Exely cancel failed', ['status'=>$resp->status(),'body'=>(string)$resp->body()]);
        return response()->json([
            'error' => [
                'code' => 'exely_cancel_failed',
                'provider_status' => $resp->status(),
                'provider_body'   => $resp->json(),
            ],
        ], $resp->status());
    }

}