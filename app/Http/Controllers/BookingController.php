<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingController extends Controller
{

    //local
    public function order(Request $request)
    {
        //dd($request->all());
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.booking.order', compact('request', 'arrival', 'departure'));
    }

    public function book_verify(Request $request)
    {
        //dd($request->all());
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
            'hotel_id' => $request->get('propertyId'),
            'room_id' => $request->get('roomTypeId'),
            'rate_id' => $request->get('ratePlanId'),
            'cancellation_id' => $request->get('cancellation_id'),
            'cancel_penalty' => $request->get('cancelPrice'),
            'arrivalDate' => $request->get('arrivalDate'),
            'departureDate' => $request->get('departureDate'),
            'currency' => $res->booking->currencyCode ?? '$',
            'title' => $request->get('firstName'),
            'phone' => $request->get('phone'),
            'email' => $request->get('email'),
            'comment' => $request->get('comment'),
            'adult' => $request->get('adultCount'),
            'child' => $request->get('child'),
            'childAges' => implode(',', $request->get('childAges')),
            'sum' => $request->get('total'),
            'status' => 'Reserved',
            'book_token' => $res->booking->number ?? $str,
            'user_id' => Auth::id() ?? '1',
        ];
        $book = Book::create($data);

        return view('pages.booking.order-reserve', compact('book'));
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
        $book = Book::where('book_token', $request->number)->firstOrFail();
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
                                        "firstName" => $request->get("name"),
                                        "lastName" => $request->get("name"),
                                        "middleName" => $request->get("name"),
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
                            "firstName" => $request->get("name"),
                            "lastName" => $request->get("name"),
                            "middleName" => $request->get("name"),
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
                                        "firstName" => $request->get("name"),
                                        "lastName" => $request->get("name"),
                                        "middleName" => $request->get("name"),
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
                            "firstName" => $request->get("name"),
                            "lastName" => $request->get("name"),
                            "middleName" => $request->get("name"),
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
            //dd($main_array);
            $response = Http::timeout(60)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->post(config('services.exely.base_url') . 'reservation/v1/bookings/verify', $main_array);
            //dd($response->object());
            $order = $response->object();
            //dd($order);

            if (!isset($order->errors)) {
                return view('pages.booking.exely.order-verify', compact('order', 'request'));
            } else {
                //dd('error');
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('pages.booking.exely.order-verify', compact('order', 'request'));
            }
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    public function book_reserve_exely(Request $request)
    {
        try {
            $placements = json_decode($request->placements, true);
            if ($placements) {
                foreach ($placements as $item) {
                    $data[] = [
                        "code" => $item['code'],
                        "count" => $item['count'],
                        "kind" => $item['kind'],
                        "minAge" => $item['minAge'],
                        "maxAge" => $item['maxAge'],
                    ];
                }
            } else {
                $data[] = [
                    "code" => $request->roomCode,
                    "count" => $request->get("adultCount"),
                ];
            }

            $childAges = array_map('intval', $request->input('childAges', []));

            if (request()->filled('childAges')) {
                $array = [
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
                                    "id" => $request->get("roomTypeId"),
                                    "placements" => $data
                                ],
                                "guests" => [
                                    [
                                        "firstName" => $request->get("firstName"),
                                        "lastName" => $request->get("lastName"),
                                        "middleName" => $request->get("firstName"),
                                        "citizenship" => "KGS",
                                        "sex" => $request->get("sex"),
                                    ]
                                ],
                                "guestCount" => [
                                    "adultCount" => $request->get("adultCount"),
                                    "childAges" => $childAges,
                                ],
                                "checksum" => $request->get("checkSum"),
                                "services" => [
                                ],
                            ]
                        ],
                        "services" => [
                        ],
                        "customer" => [
                            "firstName" => $request->get("firstName"),
                            "lastName" => $request->get("lastName"),
                            "middleName" => $request->get("firstName"),
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
                        "prepayment" => [
                            "remark" => "Payment in channel",
                            "paymentType" => "Cash",
                            "prepaidSum" => 0
                        ],
                        "bookingComments" => [
                            ''
                        ],
                        "currencyCode" => $request->get("currencyCode"),
                        "createBookingToken" => $request->get("createBookingToken"),
                    ]
                ];
            } else {

                $array = [
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
                                    "id" => $request->get("roomTypeId"),
                                    "placements" => $data
                                ],
                                "guests" => [
                                    [
                                        "firstName" => $request->get("firstName"),
                                        "lastName" => $request->get("lastName"),
                                        "middleName" => $request->get("firstName"),
                                        "citizenship" => "KGS",
                                        "sex" => $request->get("sex"),
                                    ]
                                ],
                                "guestCount" => [
                                    "adultCount" => $request->get("adultCount"),
                                    "childAges" => [],
                                ],
                                "checksum" => $request->get("checkSum"),
                                "services" => [
                                ],
                            ]
                        ],
                        "services" => [
                        ],
                        "customer" => [
                            "firstName" => $request->get("firstName"),
                            "lastName" => $request->get("lastName"),
                            "middleName" => $request->get("firstName"),
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
                        "prepayment" => [
                            "remark" => "Payment in channel",
                            "paymentType" => "Cash",
                            "prepaidSum" => 0
                        ],
                        "bookingComments" => [
                            ''
                        ],
                        "currencyCode" => $request->get("currencyCode"),
                        "createBookingToken" => $request->get("createBookingToken"),
                    ]
                ];
            }

            $response = Http::timeout(60)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->post(config('services.exely.base_url') . 'reservation/v1/bookings', $array);


            // Проверка на успешность
            if ($response->successful()) {
                $res = $response->object();
                if (!isset($res->errors)) {
                    if (request()->filled('childAges')) {
                        Book::create([
                            'hotel_id' => $request->get('propertyId'),
                            'room_id' => $request->get('roomTypeId'),
                            'arrivalDate' => $request->get('arrivalDate'),
                            'departureDate' => $request->get('departureDate'),
                            'cancellation' => $res->booking->cancellationPolicy->penaltyAmount,
                            'rate_id' => $request->get('ratePlanId'),
                            'currency' => $res->booking->currencyCode,
                            //'rateId' => $request->get('ratePlanId'),
                            'title' => $request->get('firstName'),
                            //'title2' => $request->get('roomCount'),
                            'phone' => $request->get('phone'),
                            'email' => $request->get('email'),
                            'comment' => $request->get('comment'),
                            'adult' => $request->get('adultCount'),
                            'child' => implode($request->get("childAges")),
                            'sum' => $request->get('total'),
                            'status' => 'Reserved',
                            'book_token' => $res->booking->number,
                            'user_id' => Auth::id() ?? 1,
                        ]);
                    } else {
                        $book = Book::create([
                            'hotel_id' => $request->get('propertyId'),
                            'room_id' => $request->get('roomTypeId'),
                            'arrivalDate' => $request->get('arrivalDate'),
                            'departureDate' => $request->get('departureDate'),
                            'cancellation' => $res->booking->cancellationPolicy->penaltyAmount,
                            'rate_id' => $request->get('ratePlanId'),
                            'currency' => $res->booking->currencyCode,
                            //'rateId' => $request->get('ratePlanId'),
                            'title' => $request->get('firstName'),
                            //'title2' => $request->get('roomCount'),
                            'phone' => $request->get('phone'),
                            'email' => $request->get('email'),
                            'comment' => $request->get('comment'),
                            'adult' => $request->get('adultCount'),
                            'sum' => $request->get('total'),
                            'status' => 'Reserved',
                            'book_token' => $res->booking->number,
                            'user_id' => Auth::id() ?? 1,
                        ]);
                        Log::warning('Бронь создана: ' . $book->id);
                    }
                }
                return view('pages.booking.exely.order-reserve', compact('res'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('errors.400', compact('response'));
            }

        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            // Можно вернуть дефолтный ответ или пробросить исключение дальше
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
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
                $book->where('book_token', $request->number)->update([
                    'status' => "Cancelled"
                ]);
                Log::warning('Отмена брони: ' . $book->id);

                return view('pages.booking.exely.cancel-confirm', compact('cancel'));
            }

        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());

            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

}
