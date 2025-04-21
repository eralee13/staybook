<?php

namespace App\Http\Controllers\API\Exely;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    //Reservation API

    public function orderexely(Request $request)
    {
        //dd($request->all());
        $childs = explode(',', implode($request->childAges));
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y H:i');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y H:i');

        return view('pages.exely.reservation.orderexely', compact('request', 'arrival', 'departure', 'childs'));
    }

    public function res_verify_bookings(Request $request)
    {
        $placemnts = json_decode($request->placements, true);

        //$newText = str_replace(["{", "}"], ["[", "]"], $request->placements);

        foreach ($placemnts as $item) {
            $data[] = [
                "code" => $item['code'],
                "count" => $item['count'],
                "kind" => $item['kind'],
                "minAge" => $item['minAge'],
                "maxAge" => $item['maxAge'],
            ];
        }

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
                            "childAges" => explode(',', implode($request->get("childAges"))),
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
                "prepayment" => [
                    "remark" => null,
                    "paymentType" => null,
                    "prepaidSum" => 0
                ],
                "bookingComments" => [
                    $request->get("comment"),
                ]
            ]
        ];

        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/verify', $main_array);

        $order = $response->object();

        //dd($order);

        return view('pages.exely.reservation.order-verify', compact('order', 'request'));
    }


    public function res_bookings(Request $request)
    {
        //dd($request->all());
        $placemnts = json_decode($request->placements, true);
        foreach ($placemnts as $item) {
            $data[] = [
                "code" => $item['code'],
                "count" => $item['count'],
                "kind" => $item['kind'],
                "minAge" => $item['minAge'],
                "maxAge" => $item['maxAge'],
            ];
        }

        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings', [
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
                            "name" => "Online booking",
                            "description" => "",
//                            "vat" => [
//                                "applicable" => true,
//                                "included" => true,
//                                "percent" => 20
//                            ]
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
                            "childAges" => explode(',', implode($request->get("childAges"))),
                        ],
                        "checksum" => $request->get("checkSum"),
//                        "dailyRates" => [
//                            [
//                                "priceBeforeTax" => 76.47,
//                                "date" => "2025-03-25"
//                            ]
//                        ],
                        "total" => [
                            "priceBeforeTax" => $request->get("priceBeforeTax"),
                            "taxAmount" => 0,
                            "taxes" => [
//                                [
//                                    "amount" => 0.81,
//                                    "index" => 1
//                                ]
                            ]
                        ],
                        "amenities" => [
//                            [
//                                "id" => "42965",
//                                "quantity" => 3,
//                                "name" => "Breakfast",
//                                "description" => "Breakfast at a restaurant at a special price",
//                                "totalPrice" => 9.66,
//                                "serviceTotal" => [
//                                    "priceBeforeTax" => 76.47,
//                                    "taxAmount" => 0.81,
//                                    "taxes" => [
//                                        [
//                                            "amount" => 0.81,
//                                            "index" => 1
//                                        ]
//                                    ]
//                                ],
//                                "inclusive" => false,
//                                "kind" => "Meal",
//                                "mealPlanCode" => "AllInclusive",
//                                "mealPlanName" => "All inclusive",
//                                "vat" => [
//                                    "applicable" => true,
//                                    "included" => true,
//                                    "percent" => 20
//                                ]
//                            ]
                        ],
                        "extraStayCharge" => [
//                            "earlyArrival" => [
//                                "overriddenDateTime" => "2025-03-25T14:00",
//                                "total" => [
//                                    "priceBeforeTax" => 76.47,
//                                    "taxAmount" => 0.81,
//                                    "taxes" => [
//                                        [
//                                            "amount" => 0.81,
//                                            "index" => 1
//                                        ]
//                                    ]
//                                ]
//                            ],
//                            "lateDeparture" => [
//                                "overriddenDateTime" => "2025-03-25T14:00",
//                                "total" => [
//                                    "priceBeforeTax" => 76.47,
//                                    "taxAmount" => 0.81,
//                                    "taxes" => [
//                                        [
//                                            "amount" => 0.81,
//                                            "index" => 1
//                                        ]
//                                    ]
//                                ]
//                            ]
                        ]
                    ]
                ],
                "amenities" => [
//                    [
//                        "id" => "7898",
//                        "name" => "Fruit platter",
//                        "description" => "Fruit platter at check-in",
//                        "price" => 200
//                    ]
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
                    $request->get("comment"),
                ],
                "total" => [
                    "priceBeforeTax" => $request->get("priceBeforeTax"),
                    "taxAmount" => 0,
                    "taxes" => [
//                        [
//                            "amount" => 0.81,
//                            "index" => 1
//                        ]
                    ]
                ],
                "taxes" => [
//                    [
//                        "index" => 1,
//                        "name" => "Lodging fee",
//                        "description" => "Fee per guest, payable at check-in"
//                    ]
                ],
                "currencyCode" => $request->get("currencyCode"),
                "cancellation" => [
                    "penaltyAmount" => $request->get("cancellation"),
                    "reason" => "Booking cancellation",
                    //"cancelledUtc" => "2025-03-25T12:00:00Z"
                ],
                "cancellationPolicy" => [
                    "freeCancellationPossible" => false,
                    "freeCancellationDeadlineLocal" => null,
                    "freeCancellationDeadlineUtc" => null,
                    "penaltyAmount" => $request->get("cancellation"),
                ],
                "createBookingToken" => $request->get("createBookingToken"),
//                "number" => "20191001-1024-45675262",
//                "status" => "Confirmed",
//                "createdDateTime" => "2025-03-25T12:00:00Z",
//                "modifiedDateTime" => "2025-03-25T12:00:00Z",
//                "version" => "MjAyMzA1MTktNzI5Mi0xMTc1MzI1Mi0y"
            ]
        ]);
        $res = $response->object();

        //dd($res);
        if (!isset($res->errors)) {
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
                'user_id' => Auth::id(),
            ]);
        }
        return view('pages.exely.reservation.order-booking', compact('res'));
    }

    public function res_calculate(Request $request)
    {
        $cancel = Carbon::createFromDate($request->cancelTime)->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/reservation/v1/bookings/' . $request->number . '/calculate-cancellation-penalty?cancellationDateTimeUtc=' . $cancel);
        $calc = $response->object();

        return view('pages.exely.reservation.cancel', compact('calc', 'request'));
    }

    public function res_cancel(Request $request, Book $book)
    {
        $resp = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/' . $request->number . '/cancel', [
            "reason" => "Booking cancellation",
            "expectedPenaltyAmount" => $request->amount
        ]);
        $cancel = $resp->object();


        $book->where('book_token', $request->number)->update([
            'status' => "Cancelled"
        ]);

        return view('pages.exely.reservation.cancel-confirm', compact('cancel'));
    }

//    public function res_booking()
//    {
//        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262');
//        $booking = $response->object()->errors[0];
//        dd($booking);
//    }

//    public function res_modify()
//    {
//        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262/modify', [
//            "booking" => [
//                "propertyId" => "1024",
//                "roomStays" => [
//                    [
//                        "stayDates" => [
//                            "arrivalDateTime" => "2025-03-11T14:00",
//                            "departureDateTime" => "2025-03-12T12:00",
//                        ],
//                        "ratePlan" => ["id" => "133528"],
//                        "roomType" => [
//                            "id" => "82751",
//                            "placements" => [["code" => "AdultBed-2"]],
//                        ],
//                        "guests" => [
//                            [
//                                "firstName" => "John",
//                                "lastName" => "Doe",
//                                "middleName" => "Smith",
//                                "citizenship" => "GBR",
//                                "sex" => "Male",
//                            ],
//                        ],
//                        "guestCount" => ["adultCount" => 1, "childAges" => []],
//                        "checksum" =>
//                            "eyJDaGVja3N1bVdpdGhPdXRFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9LCJDaGVja3N1bVdpdGhFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9fQ==",
//                        "amenities" => [
//                            [
//                                "id" => "42965",
//                                "quantity" => 3,
//                                "quantityByGuests" => null,
//                            ],
//                        ],
//                        "extraStay" => [
//                            "earlyArrival" => [
//                                "overriddenDateTime" => "2025-03-11T14:00",
//                            ],
//                            "lateDeparture" => [
//                                "overriddenDateTime" => "2025-03-11T14:00",
//                            ],
//                        ],
//                    ],
//                ],
//                "amenities" => [["id" => "7898"]],
//                "customer" => [
//                    "firstName" => "John",
//                    "lastName" => "Doe",
//                    "middleName" => "Smith",
//                    "citizenship" => "GBR",
//                    "contacts" => [
//                        "phones" => [["phoneNumber" => "+442012345678"]],
//                        "emails" => [["emailAddress" => "email@example.com"]],
//                    ],
//                    "comment" => "Preferably a room with a sea view",
//                ],
//                "prepayment" => [
//                    "remark" => "Payment in channel",
//                    "paymentType" => "Cash",
//                    "prepaidSum" => 0,
//                ],
//                "bookingComments" => ["Preferably a room with a sea view"],
//                "version" => "MjAyMzA1MTktNzI5Mi0xMTc1MzI1Mi0y",
//            ],
//        ]);
//        $booking = $response->object()->errors[0];
//        dd($booking);
//    }


//    public function res_verify()
//    {
//        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262/modify', [
//            "booking" => [
//                "propertyId" => "1024",
//                "roomStays" => [
//                    [
//                        "stayDates" => [
//                            "arrivalDateTime" => "2025-03-11T14:00",
//                            "departureDateTime" => "2025-03-12T12:00",
//                        ],
//                        "ratePlan" => ["id" => "133528"],
//                        "roomType" => [
//                            "id" => "82751",
//                            "placements" => [["code" => "AdultBed-2"]],
//                        ],
//                        "guests" => [
//                            [
//                                "firstName" => "John",
//                                "lastName" => "Doe",
//                                "middleName" => "Smith",
//                                "citizenship" => "GBR",
//                                "sex" => "Male",
//                            ],
//                        ],
//                        "guestCount" => ["adultCount" => 1, "childAges" => [5]],
//                        "checksum" =>
//                            "eyJDaGVja3N1bVdpdGhPdXRFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9LCJDaGVja3N1bVdpdGhFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9fQ==",
//                        "amenities" => [
//                            [
//                                "id" => "42965",
//                                "quantity" => 3,
//                                "quantityByGuests" => null,
//                            ],
//                        ],
//                        "extraStay" => [
//                            "earlyArrival" => [
//                                "overriddenDateTime" => "2025-03-11T14:00",
//                            ],
//                            "lateDeparture" => [
//                                "overriddenDateTime" => "2025-03-11T14:00",
//                            ],
//                        ],
//                    ],
//                ],
//                "amenities" => [["id" => "7898"]],
//                "customer" => [
//                    "firstName" => "John",
//                    "lastName" => "Doe",
//                    "middleName" => "Smith",
//                    "citizenship" => "GBR",
//                    "contacts" => [
//                        "phones" => [["phoneNumber" => "+442012345678"]],
//                        "emails" => [["emailAddress" => "email@example.com"]],
//                    ],
//                    "comment" => "Preferably a room with a sea view",
//                ],
//                "prepayment" => [
//                    "remark" => "Payment in channel",
//                    "paymentType" => "Cash",
//                    "prepaidSum" => 0,
//                ],
//                "bookingComments" => ["Preferably a room with a sea view"],
//                "version" => "MjAyMzA1MTktNzI5Mi0xMTc1MzI1Mi0y",
//            ],
//        ]);
//        $booking = $response->object()->errors[0];
//        dd($booking);
//
//    }

}
