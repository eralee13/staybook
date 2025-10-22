<?php

namespace App\Http\Controllers;

use App\Mail\BookCancelMail;
use App\Mail\BookMail;
use App\Models\Book;
use App\Models\Contact;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{

    //local
    public function order(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.booking.order', compact('request', 'arrival', 'departure'));
    }

    public function book_verify(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.booking.order-verify', compact('request', 'arrival', 'departure'));
    }

    public function book_reserve(Request $request)
    {
        $date = date('Ymd'); // текущая дата: 20250507
        $part1 = random_int(100000, 999999); // 6-значное число
        $part2 = random_int(1000000000, 9999999999); // 10-значное число
        $str = "{$date}-{$part1}-{$part2}";
        $data = [
            'hotel_id' => $request->get('hotel_id'),
            'room_id' => $request->get('room_id'),
            'rate_id' => $request->get('rate_id'),
            'cancellation_id' => $request->get('cancellation_id'),
            'cancel_penalty' => $request->get('cancelPrice'),
            'cancel_price_source' => $request->get('cancelPriceSource'),
            'arrivalDate' => $request->get('arrivalDate'),
            'departureDate' => $request->get('departureDate'),
            'currency' => $request->currency ?? 'USD',
            'source_sym' => $request->source_sym ?? 'USD',
            'title' => $request->get('title'),
            'child_name' => $request->get('child_name'),
            'room_count' => $request->get('roomCount'),
            'adult' => $request->get('adult'),
            'child' => $request->get('child'),
            'childages' => implode(',', $request->get('childAges')),
            'sum' => $request->get('sum'),
            'price' => $request->get('price'),
            'phone' => $request->get('phone'),
            'email' => $request->get('email'),
            'comment' => $request->get('comment'),
            'book_token' => $str,
            'user_id' => Auth::id() ?? '1',
            'checkin_request' => $request->get('checkin_request') ?? 0,
            'checkout_request' => $request->get('checkout_request') ?? 0,
            'checkin_time' => $request->get('checkin_time'),
            'checkout_time' => $request->get('checkout_time'),
            'api_type' => 'local',
            'status' => 'Reserved',
        ];
        $book = Book::create($data);
        if ($book) {
            Log::warning('Бронь создана: ' . $book->id);
            $email = Contact::first()->email;
            $hotel_email = Hotel::where('id', $book->hotel_id)->first()->email;
            Mail::to([$email, $hotel_email])
                ->cc(Auth::user()->email)
                ->bcc($book->email)
                ->send(new BookMail($book));
        }

        return view('pages.booking.order-reserve', compact('book', 'request'));
    }

    public function cancel_calculate(Request $request)
    {
        $book = Book::where('book_token', $request->number)->firstOrFail();
        return view('pages.booking.cancel-calculate', compact('book', 'request'));
    }

    public function cancel_confirm(Request $request, Book $book)
    {
        $book->where('book_token', $request->number)->update([
            'status' => "Cancelled"
        ]);

        $book = Book::where('book_token', $request->number)->first();
        if ($book) {
            Log::warning('Отмена брони: ' . $book->id);
            $email = Contact::first()->email;
            $hotel_email = Hotel::where('id', $book->hotel_id)->first()->email;
            Mail::to([$email, $hotel_email])->cc(Auth::user()->email)->bcc($book->email)->send(new BookCancelMail($book));
        }
        return view('pages.booking.cancel-confirm', compact('book', 'request'));
    }

    //exely
    public function order_exely(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y H:i');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y H:i');
        $hotel = Hotel::where('exely_id', $request->propertyId)->get()->first();
        $hotel_utc = \Carbon\Carbon::now($hotel->timezone)->format('P');
        $cancel_utc = \Carbon\Carbon::createFromDate($request->cancelUtc)->format('P');
        if (request()->filled('childAges')) {
            $childs = explode(',', implode($request->childAges));
        } else {
            $childs = [];
        }
        $utc = \Carbon\Carbon::parse($request->cancelUtc);
        $local = \Carbon\Carbon::parse($request->cancelLocal . 'Z');
        $hours = $utc->diffInHours($local, false);
        $offset = sprintf('UTC%+03d:00', $hours);

        return view('pages.booking.exely.order', compact('request', 'arrival', 'departure', 'hotel', 'hotel_utc', 'cancel_utc', 'offset', 'hotel', 'childs'));
    }

    public function book_verify_exely(Request $request)
    {
        try {
            $placements = json_decode($request->placements, true);

            foreach ($placements as $item) {
                $data[] = [
                    "code" => $item['code'],
                    "count" => $item['count'],
                    "kind" => $item['kind'],
                    "minAge" => $item['minAge'],
                    "maxAge" => $item['maxAge'],
                ];
            }

            $childAges = array_map('intval', $request->input('childAges', []));

            $childAgesInput = (array) $request->input('childAges', []);

            $childCount = collect($childAgesInput)
                ->flatMap(fn($ages) => explode(',', $ages)) // "1,6" → ["1", "6"]
                ->map(fn($age) => (int) trim($age))         // убираем пробелы и делаем числа
                ->filter(fn($age) => $age > 0)              // убираем пустые
                ->count();


            $titles = [];
            for ($i = 1; $i <= 8; $i++) {
                if (!empty($request["title{$i}"])) {
                    $titles[] = trim($request["title{$i}"]);
                }
            }
            $fullName = implode(', ', $titles);

            $childNames = [];
            for ($i = 1; $i <= 8; $i++) {
                if (!empty($request["child_name{$i}"])) {
                    $childNames[] = trim($request["child_name{$i}"]);
                }
            }
            $childName = implode(', ', $childNames);

            if (request()->filled('childAges')) {
                $main_array = [
                    "booking" => [
                        "propertyId" => $request->get("propertyId"),
                        "roomStays" => [
                            [
                                "stayDates" => [
                                    "arrivalDateTime" => $request->get("arrivalDate"),
                                    "departureDateTime" => $request->get("departureDate"),
                                ],
                                "ratePlan" => [
                                    "id" => $request->get("ratePlanId"),
                                ],
                                "roomType" => [
                                    "placements" => $data,
                                    "id" => $request->get("roomTypeId"),
                                ],
                                "guests" => [
                                    [
                                        "firstName" => $fullName,
                                        "lastName" => '-',
                                        "middleName" => $childName,
                                        "citizenship" => "KGS",
                                        "sex" => "Male"
                                    ]
                                ],
                                "guestCount" => [
                                    "adultCount" => $request->get("adultCount"),
                                    "childAges" => $childAges,
                                ],
                                "services" => [],
                                "checksum" => $request->get("checkSum"),
                            ]
                        ],
                        "services" => [],
                        "customer" => [
                            "firstName" => $fullName,
                            "lastName" => '-',
                            "middleName" => $childName,
                            "citizenship" => "KGS",
                            "contacts" => [
                                "phones" => [
                                    [
                                        "phoneNumber" => $request->get("phone"),
                                    ]
                                ],
                                "emails" => [
                                    [
                                        "emailAddress" => $request->get("email"),
                                    ]
                                ]
                            ],
                            "comment" => $request->get("comment"),
                        ],
                        "bookingComments" => [
                            '',
                        ]
                    ]
                ];
            } else {
                $main_array = [
                    "booking" => [
                        "propertyId" => $request->get("propertyId"),
                        "roomStays" => [
                            [
                                "stayDates" => [
                                    "arrivalDateTime" => $request->get("arrivalDate"),
                                    "departureDateTime" => $request->get("departureDate"),
                                ],
                                "ratePlan" => [
                                    "id" => $request->get("ratePlanId"),
                                ],
                                "roomType" => [
                                    "placements" => $data,
                                    "id" => $request->get("roomTypeId"),
                                ],
                                "guests" => [
                                    [
                                        "firstName" => $fullName,
                                        "lastName" => $fullName,
                                        "middleName" => $fullName,
                                        "citizenship" => "KGS",
                                        "sex" => "Male"
                                    ]
                                ],
                                "guestCount" => [
                                    "adultCount" => $request->get("adultCount"),
                                    "childAges" => [],
                                ],
                                "services" => [],
                                "checksum" => $request->get("checkSum"),
                            ]
                        ],
                        "services" => [],
                        "customer" => [
                            "firstName" => $fullName,
                            "lastName" => $fullName,
                            "middleName" => $fullName,
                            "citizenship" => "KGS",
                            "contacts" => [
                                "phones" => [
                                    [
                                        "phoneNumber" => $request->get("phone"),
                                    ]
                                ],
                                "emails" => [
                                    [
                                        "emailAddress" => $request->get("email"),
                                    ]
                                ]
                            ],
                            "comment" => $request->get("comment"),
                        ],
                        "bookingComments" => [
                            ''
                        ],
                        "version" => "MjAyMzA1MTktNzI5Mi0xMTc1MzI1Mi0y"
                    ]
                ];
            }

            $response = Http::timeout(60)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->post(config('services.exely.base_url') . 'reservation/v1/bookings/verify', $main_array);
            $order = $response->object();

            if (!isset($order->errors)) {
                return view('pages.booking.exely.order-verify', compact('order', 'request', 'childCount'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('pages.booking.exely.order-verify', compact('order', 'request', 'childCount'));
            }
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    public function book_reserve_exely(Request $request)
    {
        try {
            // ---------- placements ----------
            $data = [];
            $placements = json_decode($request->placements, true);
            if (is_array($placements) && count($placements)) {
                foreach ($placements as $item) {
                    $data[] = [
                        "code"   => $item['code']   ?? null,
                        "count"  => (int)($item['count'] ?? 0),
                        "kind"   => $item['kind']   ?? null,
                        "minAge" => $item['minAge'] ?? null,
                        "maxAge" => $item['maxAge'] ?? null,
                    ];
                }
            } else {
                $data[] = [
                    "code"  => $request->roomCode,
                    "count" => (int)$request->get("adultCount", 1),
                ];
            }

            // ---------- child ages ----------
            $childAgesRaw = $request->input('childAges', []);
            if (is_string($childAgesRaw)) {
                $childAgesRaw = explode(',', $childAgesRaw);
            }
            $childAges = array_map('intval', (array) $childAgesRaw);

            // ---------- cancelDate normalize ----------
            // вход: "30.10.2025 13:59 (UTC+05:00)" -> "2025-10-30T08:59:00Z"
            $cancelParsed = $this->parseCancelDatePair($request->get('cancelDate'));
            $cancelDateLocal = $cancelParsed['local'];
            $cancelDateUtc   = $cancelParsed['utc'];

            // ---------- payload ----------
            $roomStay = [
                "stayDates" => [
                    "arrivalDateTime"   => $request->get("arrivalDate"),
                    "departureDateTime" => $request->get("departureDate"),
                ],
                "ratePlan" => [
                    "id" => $request->get("ratePlanId"),
                ],
                "roomType" => [
                    "id"         => $request->get("roomTypeId"),
                    "placements" => $data,
                ],
                "guests" => [[
                    "firstName"   => $request->get("firstName"),
                    "lastName"    => '-',
                    "middleName"  => $request->get("middleName"),
                    "citizenship" => "KGS",
                    "sex"         => $request->get("sex"),
                ]],
                "guestCount" => [
                    "adultCount" => (int)$request->get("adultCount", 1),
                    "childAges"  => $childAges ?: [],
                ],
                "checksum" => $request->get("checkSum"),
            ];

            $array = [
                "booking" => [
                    "propertyId" => $request->get("propertyId"),
                    "roomStays"  => [ $roomStay ],
                    "customer"   => [
                        "firstName"   => $request->get("firstName"),
                        "lastName"    => '-',
                        "middleName"  => $request->get("middleName"),
                        "citizenship" => "KGS",
                        "contacts"    => [
                            "phones" => [[ "phoneNumber"  => $request->get("phone") ]],
                            "emails" => [[ "emailAddress" => $request->get("email") ]],
                        ],
                        "comment" => $request->get("comment"),
                    ],
                    "prepayment" => [
                        "remark"      => "Full payment made in the channel",
                        "paymentType" => "Prepay",
                        "prepaidSum"  => 0
                    ],
                    "bookingComments"   => [''],
                    "currencyCode"      => $request->get("currencyCode"),
                    "createBookingToken"=> $request->get("createBookingToken"),
                ]
            ];

            // ---------- call Exely ----------
            $base = config('services.exely.base_url') ?? config('services.exely.base'); // поддержка обоих ключей
            $resp = Http::timeout(60)
                ->withHeaders([
                    'x-api-key' => config('services.exely.key'),
                    'accept'    => 'application/json'
                ])
                ->post(rtrim($base, '/').'/reservation/v1/bookings', $array);

            if ($resp->successful()) {
                $res = $resp->object();

                if (!isset($res->errors)) {
                    // сохраняем бронь
                    $cancel = $this->normalizeCancelDateForDbAndApi($request->get('cancelDate')); // "05.11.2025 13:59 (UTC+05:00)"

                    $book = Book::create([
                        'hotel_id'             => $request->get('propertyId'),
                        'room_id'              => $request->get('roomTypeId'),
                        'arrivalDate'          => $request->get('arrivalDate'),
                        'departureDate'        => $request->get('departureDate'),
                        'cancel_date'      => $cancel['for_db_local'],
                        'cancel_date_utc'  => $cancel['for_db_utc'],
                        'cancel_tz_offset' => $cancel['tz_offset'],
                        'cancel_penalty'       => $request->get('cancel_brut_price'),
                        'cancel_price_source'  => $request->get('cancel_net_price'),
                        'rate_id'              => $request->get('ratePlanId'),
                        'currency'             => $request->get('currency') ?? $request->get('currencyCode'),
                        'source_sym'           => $request->get('source_sym'),
                        'title'                => $request->get('firstName'),
                        'child_name'           => $request->get('middleName'),
                        'phone'                => $request->get('phone'),
                        'email'                => $request->get('email'),
                        'comment'              => $request->get('comment'),
                        'adult'                => (int)$request->get('adultCount', 1),
                        'child'                => $request->get('child'),
                        'childAges'            => $request->get('childAges'), // как пришло (строка/массив)
                        'sum'                  => $request->get('brut_price'),
                        'price'                => $request->get('net_price'),
                        'status'               => 'Reserved',
                        'book_token'           => $res->booking->number ?? null,
                        'user_id'              => Auth::id() ?? 1,
                        'api_type'             => 'exely',
                    ]);

                    // письма (опционально)
                    if (class_exists(Contact::class) && class_exists(BookMail::class)) {
                        $email = optional(Contact::first())->email;
                        if ($email) {
                            $mailable = Mail::to($email);
                            if (Auth::check()) $mailable->cc(Auth::user()->email);
                            if (!empty($book->email)) $mailable->bcc($book->email);
                            $mailable->send(new BookMail($book));
                        }
                    }

                    Log::info('Бронь создана', ['id' => $book->id, 'token' => $book->book_token]);
                }

                return view('pages.booking.exely.order-reserve', compact('res','request'));
            }

            Log::warning('Запрос на бронь завершился ошибкой', ['status' => $resp->status(), 'body' => $resp->body()]);
            return view('errors.400');
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: '.$e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    /**
     * Принимает строку вида "05.11.2025 13:59 (UTC+05:00)" (или ISO),
     * возвращает:
     *  - for_db_local:  'Y-m-d H:i:s' (локальное без смещения)
     *  - for_db_utc:    'Y-m-d H:i:s' (UTC без Z)
     *  - for_api_utc:   'Y-m-d\TH:i:s\Z' (UTC для Exely)
     *  - tz_offset:     '+05:00' | null
     */
    private function normalizeCancelDateForDbAndApi(?string $raw): array
    {
        if (!$raw || !is_string($raw)) {
            return ['for_db_local'=>null,'for_db_utc'=>null,'for_api_utc'=>null,'tz_offset'=>null];
        }

        try {
            // "DD.MM.YYYY HH:MM (UTC±HH:MM)"
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})\s+(\d{2}):(\d{2})\s*\(UTC([+-]\d{2}:\d{2})\)$/', trim($raw), $m)) {
                [$all,$d,$mth,$y,$h,$i,$offset] = $m;
                $isoLocal = sprintf('%s-%s-%sT%s:%s:00%s', $y, $mth, $d, $h, $i, $offset);

                $dtLocal = new \DateTime($isoLocal);                    // локал со смещением
                $dtUtc   = (clone $dtLocal)->setTimezone(new \DateTimeZone('UTC'));

                return [
                    'for_db_local' => $dtLocal->format('Y-m-d H:i:s'),
                    'for_db_utc'   => $dtUtc->format('Y-m-d H:i:s'),
                    'for_api_utc'  => $dtUtc->format('Y-m-d\TH:i:s\Z'),
                    'tz_offset'    => $offset,
                ];
            }

            // Любой другой распознаваемый формат (ISO и т.д.)
            $dt = new \DateTime($raw);
            $offset = $dt->format('P');
            $dtUtc  = (clone $dt)->setTimezone(new \DateTimeZone('UTC'));

            return [
                'for_db_local' => $dt->format('Y-m-d H:i:s'),
                'for_db_utc'   => $dtUtc->format('Y-m-d H:i:s'),
                'for_api_utc'  => $dtUtc->format('Y-m-d\TH:i:s\Z'),
                'tz_offset'    => $offset,
            ];
        } catch (\Throwable $e) {
            \Log::warning('normalizeCancelDateForDbAndApi failed', ['input'=>$raw,'error'=>$e->getMessage()]);
            return ['for_db_local'=>null,'for_db_utc'=>null,'for_api_utc'=>null,'tz_offset'=>null];
        }
    }

    /**
     * Преобразует строку вида "30.10.2025 13:59 (UTC+05:00)"
     * в "YYYY-MM-DDTHH:MM:SSZ" (UTC). Возвращает null, если распарсить не удалось.
     */
    private function parseCancelDatePair(?string $raw): array
    {
        if (!$raw || !is_string($raw)) {
            return ['local' => null, 'utc' => null];
        }

        try {
            // Шаблон под формат "05.11.2025 13:59 (UTC+05:00)"
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})\s+(\d{2}):(\d{2})\s*\(UTC([+-]\d{2}:\d{2})\)$/', trim($raw), $m)) {
                [$all, $d, $mth, $y, $h, $i, $offset] = $m;
                $isoLocal = sprintf('%s-%s-%sT%s:%s:00%s', $y, $mth, $d, $h, $i, $offset);

                // создаём дату с учётом смещения
                $dtLocal = new \DateTime($isoLocal);
                $dtUtc   = (clone $dtLocal)->setTimezone(new \DateTimeZone('UTC'));

                return [
                    'local' => $dtLocal->format('Y-m-d\TH:i:sP'),
                    'utc'   => $dtUtc->format('Y-m-d\TH:i:s\Z'),
                ];
            }

            // Попробуем ISO-формат или любой другой
            $dt = new \DateTime($raw);
            $dtUtc = (clone $dt)->setTimezone(new \DateTimeZone('UTC'));
            return [
                'local' => $dt->format('Y-m-d\TH:i:sP'),
                'utc'   => $dtUtc->format('Y-m-d\TH:i:s\Z'),
            ];
        } catch (\Throwable $e) {
            Log::warning('parseCancelDatePair failed', ['input' => $raw, 'error' => $e->getMessage()]);
            return ['local' => null, 'utc' => null];
        }
    }


    public function cancel_calculate_exely(Request $request)
    {
        try {
            $cancel = Carbon::createFromDate(now())->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');

            $response = Http::timeout(30)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->get(config('services.exely.base_url') . 'reservation/v1/bookings/' . $request->number . '/calculate-cancellation-penalty?cancellationDateTimeUtc=' . $cancel);
            if ($response->successful()) {
                $calc = $response->object();
                return view('pages.booking.exely.cancel-order', compact('calc', 'request'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('errors.400', compact('response'));
            }
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    public function cancel_confirm_exely(Request $request, Book $book)
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->post(config('services.exely.base_url') . 'reservation/v1/bookings/' . $request->number . '/cancel', [
                    "reason" => "Booking cancellation",
                    "expectedPenaltyAmount" => $request->amount
                ]);

            if ($response->successful()) {
                $cancel = $response->object();
                $book = Book::where('book_token', $request->number)->first();
                $book->where('book_token', $request->number)->update([
                    'status' => "Cancelled"
                ]);
                Log::warning('Отмена брони: ' . $book->id);
                $email = Contact::first()->email;
                Mail::to($email)
                    ->cc(Auth::user()->email)
                    ->bcc($book->email)
                    ->send(new BookCancelMail($book));
                return view('pages.booking.exely.cancel-confirm', compact('cancel', 'request'));
            }
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

}
