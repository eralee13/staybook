<?php

namespace App\Http\Controllers\API\Exely;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    //Reservation API

    public function orderexely(Request $request)
    {
        $hotel = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $request->propertyId);
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y H:i');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y H:i');
        return view('pages.exely.reservation.orderexely', compact('request', 'arrival', 'departure','hotel'));
    }

    public function res_verify_bookings(Request $request)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/verify', [
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
                            "placements" => [
                                [
                                    "code" => $request->get("placementCode"),
                                    "count" => $request->get("roomCount"),
                                    "kind" => $request->get("roomType"),
                                    "minAge" => null,
                                    "maxAge" => null
                                ]
                            ],
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
                            "adultCount" => $request->get("guestCount"),
                            "childAges" => [
                            ]
                        ],
                        "amenities" => [
                        ],
                        "checksum" => $request->get("checkSum"),
                    ]
                ],
                "amenities" => [
                ],
                "customer" => [
                    "firstName" => $request->get("name"),
                    "lastName" => $request->get("name"),
                    "middleName" => "",
                    "citizenship" => "",
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
        ]);

        $order = $response->object();

        return view('pages.exely.reservation.order-verify', compact('order'));
    }


    public function res_bookings(Request $request)
    {
        //dd($request->all());
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
                            "placements" => [
                                [
                                    "code" => $request->get("roomCode"),
                                    //"count" => 2,
//                                    "kind" => "Adult",
//                                    "minAge" => null,
//                                    "maxAge" => null
                                ]
                            ],
                            //"name" => "Standard"
                        ],
                        "guests" => [
                            [
                                "firstName" => $request->get("firstName"),
                                "lastName" => $request->get("lastName"),
                                "middleName" => "",
                                "citizenship" => "",
                                "sex" => $request->get("sex"),
                            ]
                        ],
                        "guestCount" => [
                            "adultCount" => $request->get("guestCount"),
                            "childAges" => [

                            ]
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
                    "middleName" => "",
                    "citizenship" => "",
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
                "currencyCode" => "EUR",
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

        return view('pages.exely.reservation.order-booking', compact('res'));
    }

    public function res_booking()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262');
        $booking = $response->object()->errors[0];
        dd($booking);
    }

    public function res_modify()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262/modify', [
            "booking" => [
                "propertyId" => "1024",
                "roomStays" => [
                    [
                        "stayDates" => [
                            "arrivalDateTime" => "2025-03-11T14:00",
                            "departureDateTime" => "2025-03-12T12:00",
                        ],
                        "ratePlan" => ["id" => "133528"],
                        "roomType" => [
                            "id" => "82751",
                            "placements" => [["code" => "AdultBed-2"]],
                        ],
                        "guests" => [
                            [
                                "firstName" => "John",
                                "lastName" => "Doe",
                                "middleName" => "Smith",
                                "citizenship" => "GBR",
                                "sex" => "Male",
                            ],
                        ],
                        "guestCount" => ["adultCount" => 1, "childAges" => [5]],
                        "checksum" =>
                            "eyJDaGVja3N1bVdpdGhPdXRFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9LCJDaGVja3N1bVdpdGhFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9fQ==",
                        "amenities" => [
                            [
                                "id" => "42965",
                                "quantity" => 3,
                                "quantityByGuests" => null,
                            ],
                        ],
                        "extraStay" => [
                            "earlyArrival" => [
                                "overriddenDateTime" => "2025-03-11T14:00",
                            ],
                            "lateDeparture" => [
                                "overriddenDateTime" => "2025-03-11T14:00",
                            ],
                        ],
                    ],
                ],
                "amenities" => [["id" => "7898"]],
                "customer" => [
                    "firstName" => "John",
                    "lastName" => "Doe",
                    "middleName" => "Smith",
                    "citizenship" => "GBR",
                    "contacts" => [
                        "phones" => [["phoneNumber" => "+442012345678"]],
                        "emails" => [["emailAddress" => "email@example.com"]],
                    ],
                    "comment" => "Preferably a room with a sea view",
                ],
                "prepayment" => [
                    "remark" => "Payment in channel",
                    "paymentType" => "Cash",
                    "prepaidSum" => 0,
                ],
                "bookingComments" => ["Preferably a room with a sea view"],
                "version" => "MjAyMzA1MTktNzI5Mi0xMTc1MzI1Mi0y",
            ],
        ]);
        $booking = $response->object()->errors[0];
        dd($booking);
    }


    public function res_verify()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262/modify', [
            "booking" => [
                "propertyId" => "1024",
                "roomStays" => [
                    [
                        "stayDates" => [
                            "arrivalDateTime" => "2025-03-11T14:00",
                            "departureDateTime" => "2025-03-12T12:00",
                        ],
                        "ratePlan" => ["id" => "133528"],
                        "roomType" => [
                            "id" => "82751",
                            "placements" => [["code" => "AdultBed-2"]],
                        ],
                        "guests" => [
                            [
                                "firstName" => "John",
                                "lastName" => "Doe",
                                "middleName" => "Smith",
                                "citizenship" => "GBR",
                                "sex" => "Male",
                            ],
                        ],
                        "guestCount" => ["adultCount" => 1, "childAges" => [5]],
                        "checksum" =>
                            "eyJDaGVja3N1bVdpdGhPdXRFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9LCJDaGVja3N1bVdpdGhFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9fQ==",
                        "amenities" => [
                            [
                                "id" => "42965",
                                "quantity" => 3,
                                "quantityByGuests" => null,
                            ],
                        ],
                        "extraStay" => [
                            "earlyArrival" => [
                                "overriddenDateTime" => "2025-03-11T14:00",
                            ],
                            "lateDeparture" => [
                                "overriddenDateTime" => "2025-03-11T14:00",
                            ],
                        ],
                    ],
                ],
                "amenities" => [["id" => "7898"]],
                "customer" => [
                    "firstName" => "John",
                    "lastName" => "Doe",
                    "middleName" => "Smith",
                    "citizenship" => "GBR",
                    "contacts" => [
                        "phones" => [["phoneNumber" => "+442012345678"]],
                        "emails" => [["emailAddress" => "email@example.com"]],
                    ],
                    "comment" => "Preferably a room with a sea view",
                ],
                "prepayment" => [
                    "remark" => "Payment in channel",
                    "paymentType" => "Cash",
                    "prepaidSum" => 0,
                ],
                "bookingComments" => ["Preferably a room with a sea view"],
                "version" => "MjAyMzA1MTktNzI5Mi0xMTc1MzI1Mi0y",
            ],
        ]);
        $booking = $response->object()->errors[0];
        dd($booking);

    }

    public function res_cancel(Request $request)
    {
        $resp = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/' . $request->number . '/cancel', [
            "reason" => "Booking cancellation",
            "expectedPenaltyAmount" => 0
        ]);
        $cancel = $resp->object();

        return view('pages.exely.reservation.cancel-confirm', compact('cancel'));
    }

    public function res_calculate(Request $request)
    {
        //dd($request->all());
        $cancel = Carbon::createFromDate($request->cancelTime)->format('Y-m-d\Th:i:sT');
        //dd($cancel);
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/reservation/v1/bookings/' . $request->number . '/calculate-cancellation-penalty?cancellationDateTimeUtc=' . $cancel);
        $calc = $response->object();


        return view('pages.exely.reservation.cancel', compact('calc', 'request'));

    }


}
