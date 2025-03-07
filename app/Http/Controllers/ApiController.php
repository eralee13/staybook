<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    //exely api
    public function properties()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties?count=20&include=All');
        $properties = $response->object()->properties;
        //dd($properties);
        return view('pages.exely.properties', compact('properties'));
    }

    public function property($property)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $property);
        $property = $response->object();
        //dd($property);

        return view('pages.exely.property', compact('property'));
    }

    public function meals()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/meal-plans');
        $meals = $response->object();

        return view('pages.exely.meals', compact('meals'));
    }

    public function roomtypes()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/room-type-categories');
        $types = $response->object();

        return view('pages.exely.roomtypes', compact('types'));
    }

    public function amenities()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/room-amenity-categories');
        $amenities = $response->object();

        return view('pages.exely.amentities', compact('amenities'));
    }

    public function extrarules()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/500803/extra-stay-rules');
        $rules = $response->object()->extraStayRules;
        dd($rules);

        return view('pages.exely.extrarules', compact('rules'));
    }

    //Search API
    public function search_property(Request $request)
    {
        $response = Http::accept('application/json')->withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/room-stays/search', [
            "propertyIds" => ["1024"],
            "adults" => $request->adults,
            "childAges" => [$request->childAges],
            "include" => "",
            "arrivalDate" => $request->arrivalDate,
            "departureDate" => $request->departureDate,
            "mealPreference" => [
                "mealType" => "MealOnly",
                "mealsIncluded" => ["mealPlanCodes" => ["BreakFast"]],
            ],
            "pricePreference" => [
                "currencyCode" => "USD",
                "minPrice" => 0,
                "maxPrice" => 10000,
            ],
            //"corporateCodes" => ["string"],
        ]);
        $errors = $response->object();
        dd($errors);
        //dd($response);
        $errors = $response->object()->errors[0];
        return view('pages.exely.search', compact('errors'));
    }

    public function search_roomstays(Request $request)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])
            ->get('https://connect.test.hopenapi.com/api/search/v1/properties/'.$request->title.'/room-stays?arrivalDate='.$request->arrivalDate.'&departureDate='.$request->departureDate.'&adults='.$request->adults.'&includeExtraStays=false&includeExtraServices=false
');
        $rooms = $response->object()->roomStays;
        $rooms = collect($rooms)->sortBy('total')->values()->all();


        return view('pages.exely.search-roomstays', compact('rooms'));
    }


    public function search_services(Request $request)
    {
        $response = Http::accept('application/json')->withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/500803/services', [
            "stayDates" => [
                "arrivalDateTime" => "2025-03-07T14:00",
                "departureDateTime" => "2025-03-08T12:00",
            ],
            "roomType" => ["id" => "82751", "placements" => [["code" => "AdultBed-2"]]],
            "ratePlan" => ["id" => "987657", "corporateCodes" => ["string"]],
            "guestCount" => ["adultCount" => 1, "childAges" => [2]],
        ]);
        $services = $response->object();
        //dd($services);

        return view('pages.exely.search-services', compact('services'));
    }


    public function search_extrastays()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/search/v1/properties/500803/extra-stays',
            [
                "stayDates" => [
                    "arrivalDateTime" => "2025-03-10T14:00",
                    "departureDateTime" => "2025-03-11T12:00",
                ],
                "roomType" => ["id" => "82751", "placements" => [["code" => "AdultBed-2"]]],
                "ratePlan" => ["id" => "987657", "corporateCodes" => ["string"]],
                "guestCount" => ["adultCount" => 1, "childAges" => [5]
                ],]);
        $stays = $response->object();
        //dd($stays);

        return view('pages.exely.search-extrastays', compact('stays'));
    }

    //Reservation API
    public function res_bookings()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings', [
            "booking" => [
                "propertyId" => "500803",
                "roomStays" => [
                    [
                        "stayDates" => [
                            "arrivalDateTime" => "2025-03-18T14:00",
                            "departureDateTime" => "2025-03-19T12:00",
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
                        "services" => [
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
                "services" => [["id" => "7898"]],
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
                "createBookingToken" => "QUNERjMyQjctNTQyNi00NTdELTk0QzItQTU0Mjc0QTY0RThD",
            ],
        ]);

        $bookings = $response->object();
        dd($bookings);
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
                        "services" => [
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
                "services" => [["id" => "7898"]],
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
                        "services" => [
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
                "services" => [["id" => "7898"]],
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

    public function res_cancel()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262/cancel', [
            "reason" => "Booking cancellation",
            "expectedPenaltyAmount" => 0
        ]);
        $rules = $response->object()->errors[0];
        dd($rules);

        return view('pages.exely.extrarules', compact('rules'));
    }

    public function res_calculate()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/reservation/v1/bookings/20191001-1024-45675262/calculate-cancellation-penalty?cancellationDateTimeUtc=2025-02-11T14%3A00%3A00Z');
        $calc = $response->object()->errors[0];

        dd($calc);
    }

    public function res_verify_bookings(Request $request)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->post('https://connect.test.hopenapi.com/api/reservation/v1/bookings/verify', [
            "booking" => [
                "propertyId" => "500803",
                "roomStays" => [
                    [
                        "stayDates" => [
                            "arrivalDateTime" => $request->get("arrivalDate"),
                            "departureDateTime" => $request->get("departureDate"),
                        ],
                        "ratePlan" => ["id" => "133528"],
                        "roomType" => [
                            "id" => "82751",
                            "placements" => [["code" => "AdultBed-2"]],
                        ],
                        "guests" => [
                            [
                                "firstName" => $request->get("title"),
                                "lastName" => $request->get("title"),
                                "middleName" => $request->get("title"),
                                "citizenship" => "GBR",
                                "sex" => "Male",
                            ],
                        ],
                        "guestCount" => ["adultCount" => $request->get("count"), "childAges" => [5]],
                        "checksum" =>
                            "eyJDaGVja3N1bVdpdGhPdXRFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9LCJDaGVja3N1bVdpdGhFeHRyYXMiOnsiVG90YWxBbW91bnRBZnRlclRheCI6IjU1LjUwIiwiQ3VycmVuY3lDb2RlIjoiR0JQIiwiU3RhcnRQZW5hbHR5QW1vdW50IjoiOS43MiJ9fQ==",
                        "services" => [
                            [
                                "id" => "5001625",
                                "quantity" => 2,
                                "quantityByGuests" => null,
                            ],
                        ],
                        "extraStay" => [
                            "earlyArrival" => [
                                "overriddenDateTime" => "2025-03-20T14:00",
                            ],
                            "lateDeparture" => [
                                "overriddenDateTime" => "2025-03-20T14:00",
                            ],
                        ],
                    ],
                ],
                "services" => [["id" => "7898"]],
                "customer" => [
                    "firstName" => $request->get("title"),
                    "lastName" => $request->get("title"),
                    //"middleName" => $request->get("title"),
                    //"citizenship" => "GBR",
                    "contacts" => [
                        "phones" => [["phoneNumber" => $request->get("phone"),]],
                        "emails" => [["emailAddress" => $request->get("email"),]],
                    ],
                    "comment" => "Preferably a room with a sea view",
                ],
                "prepayment" => [
                    "remark" => "Payment in channel",
                    "paymentType" => "Cash",
                    "prepaidSum" => 0,
                ],
                "bookingComments" => ["Preferably a room with a sea view"],
            ]]);
        $calc = $response->object();
        dd($calc);
    }

    public function orderexely(Request $request)
    {
        return view('pages.exely.orderexely', compact('request'));
    }
}
