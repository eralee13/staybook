<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Book;
use App\Models\Room;
use App\Models\Rate;
use App\Models\CancellationRule;


class SalesEtgController extends Controller
{
    public $keyId, $apiKey, $url, $selUrl, $language, $coef;
    public $hotelDetail, $hotelLocalData, $hotels;
    public $guestsall, $childs_name;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
        $this->coef = config('app.main_coef');
        $this->language = app()->getLocale();
    }

    public function searchHotels(Request $request)
    {
        //Emerging Sales Hotel Search implementation
        // request sample for staybook
        // {
        //     "hotel_ids": "1253", // local ids
        //     "region_id": 3421,
        //     "check_in": "2025-10-29",
        //     "check_out": "2025-10-30",
        //     "residency": "gb",
        //     "language": "ru",
        //     "guests_groups": [
        //         {
        //             "adults": 1,
        //             "children_ages": [5]
        //         }
        //     ]
        // }

        if( !empty( $request->hotel_ids ) ){

            $ids = explode(',', $request->hotel_ids);

            $this->hotels = Hotel::whereIn('id', $ids)
                ->whereNotNull('emerging_id')
                ->pluck('emerging_id')
                ->toArray();

            $this->selUrl = $this->url . '/search/serp/hotels/';

        }else{

            $this->hotels = Hotel::where('city', $request->city)
                ->whereNotNull('emerging_id')
                ->pluck('emerging_id')
                ->toArray();

            $this->selUrl = $this->url . '/search/serp/region/';

        }


        // PaxRooms (информация о размещении гостей)
        // Получаем JSON из запроса
        $groups = collect($request->input('guests_groups'));

            $paxRooms = $groups->map(function ($group) {
                $room = [
                    "adults" => (int) $group['adults'],
                ];

                // Добавляем детей, если они есть
                if (!empty($group['children_ages']) && $group['children_ages'] > 0) {
                    // $room["children"] = (int) $group['child'];
                    $room["children"] = $group['children_ages'] ?? [];
                }

                return $room;
            })->values()->toArray();

                // Общая статистика
                // $totalAdults = $groups->sum('adults');
                // $totalChildren = $groups->sum('children_ages');
                // $totalRooms = $groups->count();
        


        try {

            if ( !empty( $request->hotel_ids ) ) {
                $mainParams = [
                    "hids" => $this->hotels,
                ];
            } else {
                $mainParams = [
                    "region_id" => (int)$request->region_id, //city id
                ];
            }

            $payload = array_merge($mainParams, [
                "checkin" => $request->check_in,
                "checkout" => $request->check_out,
                "residency" => $request->residency ?? '',
                "language" => $request->language ?? 'ru',
                "guests" => $paxRooms,
                "timeout" => 30,
                "currency" => "USD"
            ]);

            // return $payload->json();

                $response = Http::timeout(31)->withBasicAuth($this->keyId, $this->apiKey)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->selUrl, $payload);

            Log::channel('emerging_sales')->info('Sales API: Search Hotels - Payload ', $payload);
            // Log::channel('emerging_sales')->info('Sales API: Search Hotels - Response ', $response->json());
            
            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Search Hotels - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }

    }

    public function searchRates(Request $request)
    {
        //Emerging Search Actualize Hotels
        // request sample for staybook
        // {
        //     "hid": "1253", // local ids
        //     "check_in": "2025-10-29",
        //     "check_out": "2025-10-30",
        //     "residency": "gb",
        //     "language": "ru",
        //     "guests_groups": [
        //         {
        //             "adults": 1,
        //             "children_ages": [5]
        //         }
        //     ]
        // }

        // PaxRooms (информация о размещении гостей)
        // Получаем JSON из запроса
        $groups = collect($request->input('guests_groups'));

            $paxRooms = $groups->map(function ($group) {
                $room = [
                    "adults" => (int) $group['adults'],
                ];

                // Добавляем детей, если они есть
                if (!empty($group['children_ages']) && $group['children_ages'] > 0) {
                    // $room["children"] = (int) $group['child'];
                    $room["children"] = $group['children_ages'] ?? [];
                }

                return $room;
            })->values()->toArray();

        try {

            $payload = [
                "hid" => (int) $request->hid, // etg hotel id
                "checkin" => $request->check_in,
                "checkout" => $request->check_out,
                "residency" => $request->residency ?? '',
                "language" => $request->language ?? 'ru',
                "guests" => $paxRooms,
                "timeout" => 30,
                "currency" => "USD"
            ];

            Log::channel('emerging_sales')->info('Sales API: Rate Actualize /search/hp/ - Payload ', $payload);

                $response = Http::timeout(31)->withBasicAuth($this->keyId, $this->apiKey)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->url . '/search/hp/', $payload);

            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Rate Actualize /search/hp/ - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }
    }

    public function preBook(Request $request)
    {
        // Pre Book implementation
        // request sample for staybook
        // {
            // "timeout": 20,
            // "book_hash": "asdasdasdasdasd",
            // "increase_percent": 0
        // }

        try {

            $payload = [
                "timeout" => $reguest->timeout ?? 20,
                "hash" => $request->book_hash, // from search rates field 'book_hash'
                "price_increase_percent" => (int) $request->increase_percent ?? 0, // optional
            ];
            
                $response = Http::timeout(21)->withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                    ->post($this->url . '/hotel/prebook/', $payload);

            Log::channel('emerging_sales')->info('Sales API: Pre Book - Payload ', $payload);
            Log::channel('emerging_sales')->info('Sales API: Pre Book - Response ', $response->json());
            

            // Возвращаем JSON
            return $response->json();


        } catch (\Throwable $th) {

            Log::channel('emerging_sales')->error('Sales API: Pre Book - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }

    }
    
    public function orderProcess(Request $request)
    {
        // Emerging Sales Order Process
        // request sample for staybook
        // {
        //     "partner_order_id": "VHeUuoFtZcPaIaG1lX2wVlKgBXaEGZhN0ds6oNiF",
        //     "book_hash": "h-027a29bc-eb54-5a23-a73c-07261af22c1b",
        //     "language": "en" // translations data
        //     "timeout": 30, // optional  
        //     "user_ip": "94.143.197.1"
        // }
        
        try {
            
            $payload = [
                "partner_order_id" => $request->partner_order_id,
                "book_hash" => $request->book_hash,
                "language" => $request->language ?? 'ru',
                "user_ip" => $request->user_ip ?? $request->ip(),
                "timeout" => 30,
            ];

                $response = Http::timeout(31)->withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                    ->post($this->url . '/hotel/order/booking/form/', $payload);
            
            Log::channel('emerging_sales')->info('Sales API: Order Process - Payload ', $payload);
            Log::channel('emerging_sales')->info('Sales API: Order Process - Response ', $response->json());

            
            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Order Process - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }

    }
    
    public function orderFinish(Request $request)
    {
        // Emerging Sales Finish Order
        // request sample for staybook
        // {
            // "timeout": 60,
            // "language": "en", // user language
            // "user": {
            //     "email": "api@staybook.asia",
            //     "comment": "Some comment",
            //     "phone": "+1234567890",
            // },
            // "partner": {
            //     "partner_order_id": "VHeUuoFtZcPaIaG1lX2wVlKgBXaEGZhN0ds6oNiF",
            //     "comment": "Some partner comment",
            //     "amount_sell_b2b2c": 150.00
            // },
            // "supplier_data": {
            //     "partner_first_name": "Petera",
            //     "partner_last_name": "Collinsa",
            //     "partner_phone": "12124567880",
            //     "partner_email": "test@staybook.asia"
            // },
            // "rooms": [
            //     {
            //         "guests" : [
            //             {
            //                 "first_name": "John",
            //                 "last_name": "Doe",
            //             },
            //             {
            //                 "first_name": "Jane",
            //                 "last_name": "Doe",
            //                  "is_child": true,
            //                  "age": 8
            //             }
            //           ]
            //      }
            // ],
            // "payment_type": {
            //     "type": "deposit",
            //     "amount": 150.00,
            //     "currency_code": "USD"
            // }
        // }


        $data = $request->input('rooms', []); // входные данные из запроса
        $rooms = [];
        
        foreach ($data as $group) {
            $quests = [];

            if (!empty($group['guests'])) {
                foreach ($group['guests'] as $index => $pax) {
                    $quest = [
                        'first_name' => $pax['first_name'] ?? '',
                        'last_name'  => $pax['last_name'] ?? '',
                    ];

                    // Определяем — ребёнок или взрослый
                    if (!empty($pax['is_child']) && $pax['is_child'] == true) {
                        $quest['is_child'] = true;

                        // Привязываем возраст, если он есть
                        if (!empty($pax['age'])) {
                            $quest['age'] = $pax['age'];
                        }
                    }

                    $quests[] = $quest;
                }
            }

            $rooms[] = ['guests' => $quests];
        }
   
        try {
            
            $payload = [
                "timeout" => 60,
                "user" => [
                        "email" => 'itsupport@staybook.asia', //$request->email, 
                        "comment" => $request->input('user.comment'), 
                        "phone" => $request->input('user.phone') 
                    ],
                "supplier_data" => [
                            "first_name_original" => $request->input('supplier_data.partner_first_name'), 
                            "last_name_original" => $request->input('supplier_data.partner_last_name'), 
                            "phone" => $request->input('supplier_data.partner_phone'), 
                            "email" => $request->input('supplier_data.partner_email') 
                        ], 
                "partner" => [
                        "partner_order_id" => $request->input('partner.partner_order_id'), 
                        "comment" => $request->input('partner.comment') ?? '', 
                        "amount_sell_b2b2c" => $request->input('partner.amount_sell_b2b2c')
                    ], 
                "language" => $request->language ?? 'ru', 
                "rooms" => $rooms, 
                "payment_type" => [
                        "type" => $request->input('payment_type.type') ?? 'deposit', 
                        "amount" => $request->input('payment_type.amount'), 
                        "currency_code" => $request->input('payment_type.currency_code') 
                    ], 
            ];
                            
            // return $payload;

                $response = Http::timeout(61)->withBasicAuth($this->keyId, $this->apiKey)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->url . '/hotel/order/booking/finish/', $payload);

            Log::channel('emerging_sales')->info('Sales API: Finish Order - Payload ', $payload);
            Log::channel('emerging_sales')->info('Sales API: Finish Order - Response ', $response->json());

            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Finish Order - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }
    }

    public function orderStatus(Request $request){

        // Emerging Sales Finish Status
        // request sample for staybook
        // {
        //     "partner_order_id": "VHeUuoFtZcPaIaG1lX2wVlKgBXaEGZhN0ds6oNiF"
        //     // "timeout": 60, // required
        // }

        try {
            
            $response = Http::timeout(61)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->post($this->url . '/hotel/order/booking/finish/status/', [

                    "partner_order_id" => $request->partner_order_id,
                    "timeout" => 60, 
                    
                ]);

            Log::channel('emerging_sales')->info('Sales API: Finish Status - Payload ', ["partner_order_id" => $request->token]);
            Log::channel('emerging_sales')->info('Sales API: Finish Status - Response ', $response->json());
            
            // Возвращаем JSON
            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Finish Status - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }

    }

    public function searchOrder(Request $request)
    {
        //Getting Success Emerging Order list
        // request sample for staybook
        // {
        //     "page_size" : 3,
        //     "page_number" : 1,
        //     "from_date" : "2024-05-01",
        //     "language": "en" // translations data
        // }

        try {

            $response = Http::timeout(31)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->post($this->url . '/hotel/order/info/', [
                    "ordering" => [
                        "ordering_type" => "desc",
                        "ordering_by" => "created_at"
                    ],
                    "pagination" => [
                        "page_size" => 3,
                        "page_number" => 1
                    ],
                    "search" => [
                        "created_at" =>[
                            "from_date" => $request->from_date . 'T00:00',
                        ]
                    ],
                    "language" => $request->language ?? 'ru',
                ]);

            Log::channel('emerging_sales')->info('Sales API: Search Order - Payload ', ["partner_order_id" => $request->partner_id]);
            Log::channel('emerging_sales')->info('Sales API: Search Order - Response ', $response->json());

            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Search Order - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }

    }

    public function cancelOrder(Request $request)
    {
        // Emerging Sales Cancel Order
        // request sample for staybook
        // {
        //     "partner_order_id": "8473727asdasd"
        //     // "timeout": 30, // optional
        // }

        try {

            $response = Http::timeout(31)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->post($this->url . '/hotel/order/cancel/', [

                    "partner_order_id" => $request->partner_order_id, 
                    "timeout" => 30,
                ]);

            Log::channel('emerging_sales')->info('Sales API: Cancel Order - Payload ', ["partner_order_id" => $request->partner_id]);
            Log::channel('emerging_sales')->info('Sales API: Cancel Order - Response ', $response->json());


            return $response->json();


        } catch (\Throwable $th) {
            
            Log::channel('emerging_sales')->error('Sales API: Cancel Order - Catch ', [$th->getMessage()]);

            return $th->getMessage();
        }
    }

    
}