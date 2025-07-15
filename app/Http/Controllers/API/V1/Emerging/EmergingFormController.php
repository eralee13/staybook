<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use App\Models\Hotel;
use App\Models\Book;
use App\Models\Room;
use App\Models\Rate;
use App\Models\CancellationRule;



class EmergingFormController extends Controller
{
    public $keyId, $apiKey, $url;
    public $hotelDetail, $hotelLocalData, $hotels;
    public $guestsall;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
        $this->coef = config('app.main_coef');
    }

    public function EmergingGetHotels(Request $request)
    {
        // get local hotels by city
        $expl = explode('-', $request->city);
        $query = Hotel::where('city', $expl[1]);
        $query->where('emerging_id', '!=', null);

        if ($request->rating){
            $query->where('rating', '=', (int)$request->rating);   
        }
        // if ($this->early_in){
        //     $query->where('early_in', $this->early_in);   
        // }
        // if ($this->early_out){
        //     $query->where('early_out', '>=', $this->early_out);   
        // }
        
        $query->with(['images', 'amenity']);

        $this->hotelLocalData = $query->get()
            ->mapWithKeys(fn($hotel) => [$hotel->emerging_id => $hotel])
            ->toArray();

            // return $this->hotelLocalData;



        //  get hotels by city from api
        $this->hotelDetail = $this->searchHotelsByCity($request);

        // dd($this->hotelDetail);


        if( isset($this->hotelDetail['data']['hotels']) ){

            // merge array local to api 
            foreach ($this->hotelDetail['data']['hotels'] as &$hotele) {
                $hotelCode = $hotele['hid'];
            
                if (isset($this->hotelLocalData[$hotelCode])) {
                    // Объединяем данные
                    $hotele['localData'] = $this->hotelLocalData[$hotelCode];
                } else {
                    // Если нет локальных данных, добавляем null
                    $hotele['localData'] = null;
                }
            }
            unset($hotele); // Разрываем ссылку, чтобы избежать проблем

            return $this->hotelDetail;
        }

        // return $this->hotelDetail;
    }

    public function searchHotelsByCity(Request $request)
    {
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $adults = 0;
        $allChildAges = [];
        $childs = 0;
        $roomCount=0;
        $guests = [];

        foreach ($rooms as $room) {
            $roomCount++;
            // Взрослые
            $adults += (int) ($room['adults'] ?? 0);
            $adultse = (int) ($room['adults'] ?? 0);

            $children = [];
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $children[] = (int) $age;
                    $allChildAges[] = (int) $age;
                    $childs++;
                }
            }

            $guests[] = [
                'adults' => $adultse,
                'children' => $children,
            ];
        }

            $response = Http::withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/search/serp/region/', [
                    "checkin" => $request->arrivalDate,
                    "checkout" => $request->departureDate,
                    "residency" => "gb",
                    "language" => "en",
                    "guests" => $guests,
                    // "timeout" => 30,
                    "region_id" => 3421,
                    "currency" => "USD"
                ]);
                // dd($response->json());
            return $response->json();
                
    }

    public function searchRates(Request $request)
    {
        // dd($request);
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $adults = 0;
        $allChildAges = [];
        $childs = 0;
        $roomCount=0;
        $guests = [];

        foreach ($rooms as $room) {
            $roomCount++;
            // Взрослые
            $adults += (int) ($room['adults'] ?? 0);
            $adultse = (int) ($room['adults'] ?? 0);

            $children = [];
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $children[] = (int) $age;
                    $allChildAges[] = (int) $age;
                    $childs++;
                }
            }

            $guests[] = [
                'adults' => $adultse,
                'children' => $children,
            ];
        }



            $response = Http::withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/search/hp/', [
                    "checkin" => $request->arrivalDate,
                    "checkout" => $request->departureDate,
                    "residency" => "gb",
                    "language" => "en",
                    "guests" => $guests,
                    "timeout" => 30,
                    "hid" => (int)$request->apiHotelId,
                    "currency" => "USD"
                ]);

            return $response->json();

    }
    
    public function startProcess(Request $request)
    {   
        $userId = Auth::id();
        $language = app()->getLocale();
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $adults = 0;
        $allChildAges = [];
        $childs = 0;
        $roomCount=0;
        $guests = [];

            foreach ($rooms as $room) {
                $roomCount++;
                // Взрослые
                $adults += (int) ($room['adults'] ?? 0);
                $adultse = (int) ($room['adults'] ?? 0);

                $children = [];
                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                    foreach ($room['childAges'] as $age) {
                        $children[] = (int) $age;
                        $allChildAges[] = (int) $age;
                        $childs++;
                    }
                }

                $guests[] = [
                    'adults' => $adultse,
                    'children' => $children,
                ];
            }

            $mappingMeals = [
                // 1 => Room Only
                'nomeal' => 1,
                'room-only' => 1,

                // 2 => Bed & Breakfast
                'breakfast' => 2,
                'buffet' => 2,
                'american-breakfast' => 2,
                'asian-breakfast' => 2,
                'chinese-breakfast' => 2,
                'continental-breakfast' => 2,
                'english-breakfast' => 2,
                'irish-breakfast' => 2,
                'israeli-breakfast' => 2,
                'japanese-breakfast' => 2,
                'scandinavian-breakfast' => 2,
                'scottish-breakfast' => 2,
                'breakfast-for-1' => 2,
                'breakfast-for-2' => 2,

                // 3 => Half Board
                'half-board' => 3,
                'half-board-dinner' => 3,
                'half-board-lunch' => 3,
                'some-meal' => 3,

                // 4 => Full Board
                'full-board' => 4,
                'lunch' => 4,
                'dinner' => 4,

                // 5 => All Inclusive
                'all-inclusive' => 5,
                'soft-all-inclusive' => 5,
                'super-all-inclusive' => 5,
                'ultra-all-inclusive' => 5,
            ];


        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/form/', [
                "partner_order_id" => $request->token,
                "book_hash" => $request->book_hash,
                "language" => "en",
                "user_ip" => $request->ip(),
            ]);

        $res = json_decode( $response->body() );
            
            // Проверка на существование локального брони
        $existbook = Book::where('book_token', $request->token)->first();

        if( !$existbook && isset($res->data->item_id) ){

            $item_id = $res->data->item_id;
            $order_id = $res->data->order_id;
            $etoken = $res->data->partner_order_id;
            $amount; $curr; $paystype;
        
            foreach( $res->data->payment_types as $paytype ){

                    // "amount" => "225"
                    // "currency_code" => "USD"
                    // "is_need_credit_card_data" => false
                    // "is_need_cvc" => false
                    // "recommended_price" => null
                    // "type" => "deposit" || now

                    if( $paytype->currency_code == 'USD'){
                        $amount = $paytype->amount;
                        $curr = $paytype->currency_code;
                        $paystype = $paytype->type;
                    }
            }

            $room = Room::where('title_en', $request->room_name)->first();
            
            if ( empty($room) ){

                $room = Room::updateOrCreate(
                    [
                        'title_en' => $request->room_name,
                        'hotel_id' => $request->hotel_id,
                    ],
                    [
                        'title' => $request->room_name,
                        'title_en' => $request->room_name,
                        // 'description_en' => $request->rate_name
                    ],
                    
                );
                
            }

                for ($i = 1; $i <= $roomCount; $i++) {
                    $fname = $request->input('paxfname' . ($i > 1 ? $i : ''));
                    $lname = $request->input('paxlname' . ($i > 1 ? $i : ''));
                    
                    if ($fname || $lname) {
                        $this->guestsall[] = trim("$fname $lname");
                    }
                }
            

                    $guests = implode(',', $this->guestsall ?? []);
                    
                    $childAges = implode(',', $allChildAges ?? []);

                    // $offset = str_replace('UTC', '', $this->utc); // '+3'

                    // Преобразуем в +03:00
                    // $formattedOffset = sprintf('%+03d:00', (int)$offset);

                    // Получаем текущую дату/время в нужной зоне
                    $utcdatetime = Carbon::now($request->utc);

                    // Форматируем результат
                    $utcdatetime = $utcdatetime->format('Y-m-d H:i:s');

                    $cancelDate = $request->cancelDate; 
                    if( $cancelDate ){
                        $cancelDate = Carbon::parse($request->cancelDate)->format('Y-m-d H:i:s');
                    }
            
                    
                $ruleid;
                if ($request->refundable == true){
                    // создаем и привязываем к rate
                    $rule = CancellationRule::create(
                        [   
                            "title" => 'Бесплатная отмена до указанной даты',
                            // "title_en" => 'Free cancellation until the specified date',
                            "is_refundable" => 1,
                            "free_cancellation_days" => 0,
                            "penalty_type" => 'fixed',  
                            "penalty_amount" => $request->cancelPrice ?? 0,
                            "end_date" =>  $cancelDate ?? $utcdatetime,
                            "description" => '',
                            "hotel_id" => $request->hotel_id,
                        ]
                    );

                    $ruleid = $rule->id ?? null;
                }else{
                    
                    $rule = CancellationRule::create(
                        [   
                            "title" => 'Безвозвратный тариф',
                            // "title_en" => 'Free cancellation until the specified date',
                            "is_refundable" => 0,
                            "free_cancellation_days" => 0,
                            "penalty_type" => 'fixed',  
                            "penalty_amount" => $request->sum ?? 0,
                            "end_date" => null,
                            "description" => '',
                            "hotel_id" => $request->hotel_id,
                        ]
                    );

                    $ruleid = $rule->id ?? null;
                }

                    $totalPrice = number_format(($request->price * $this->coef) + $request->price, 2, '.', '');
                    
                    $rate = Rate::Create(
                        [
                            // 'rate_code' => $request->rate_code,
                            'hotel_id' => $request->hotel_id,
                            'room_id' => $room->id,
                            'title' => $request->rate_name ?? '',
                            'title_en' => $request->rate_name ?? '',
                            'desc_en' => null,
                            'bed_type' => $request->bedTypeDesc,
                            'meal_id' => $mappingMeals[$request->meal_id] ?? '',
                            'allotment' => null,
                            'adult' => $adults ?? 1,
                            'child' => $childs ?? 0,
                            'children_allowed' => 0,
                            'free_children_age' => 0,
                            'currency' => $curr,
                            'price' => $request->price,
                            'price2' => null,
                            'child_extra_fee' => 0,
                            'availability' => 0,
                            'total_price' => $totalPrice,
                            'cancellation_rule_id' => $ruleid ?? null,
                            
                        ]
                    );

                    $book = Book::firstOrCreate(
                        [
                            'book_token' => $etoken,
                        ],
                        [
                            'title' => $guests,
                            'title2' => '',
                            'hotel_id' => $request->hotel_id,
                            'room_id' => $room->id ?? null,
                            'rate_id' => $rate->id ?? null,
                            'phone' => $request->phone,
                            'email' => $request->email,
                            'comment' => $request->comment,
                            'adult' => $adults ?? 1,
                            'child' => $childs,
                            'childages' => $childAges ?? '',
                            'price' => $request->price,
                            'sum' => $totalPrice,
                            'utc' => $request->utc,
                            'cancellation_id' => $ruleid,
                            'cancel_penalty' => $request->cancelPrice,
                            'currency' => $curr,
                            'cancel_date' => $cancelDate ?? $utcdatetime,
                            'arrivalDate' => $request->arrivalDate,
                            'departureDate' => $request->departureDate,
                            'status' => 'Pending',
                            'user_id' => $userId,
                            'api_type' => 'emerging',
                            'agent_ref' => '',
                        ]
                    );

            if ( !isset($book->id) ){
                return ['status' => 'error', 'error' => 'error_dublicate_local'];
            }
        }

            // dd( json_decode($response->body()) );
        return $response->json();

    }
    
    public function bookingFinish(Request $request, $data)
    {
        $language = app()->getLocale();
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $adults = 0;
        $allChildAges = [];
        $childs = 0;
        $roomCount=0;
        $guests = [];

            foreach ($rooms as $room) {
                $roomCount++;
                // Взрослые
                $adults += (int) ($room['adults'] ?? 0);
                $adultse = (int) ($room['adults'] ?? 0);

                $children = [];
                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                    foreach ($room['childAges'] as $age) {
                        $children[] = (int) $age;
                        $allChildAges[] = (int) $age;
                        $childs++;
                    }
                }

                $guests[] = [
                    'adults' => $adultse,
                    'children' => $children,
                ];
            }

                    $paxList = [];

                        for ($i = 0; $i < $roomCount; $i++) {
                            $j = $i + 1;

                            if($j > 1){

                                $paxList[] = [
                                    "first_name" => $request->{'paxfname' . $j},
                                    "last_name" => $request->{'paxlname' . $j},
                                ];
                                    
                            }else{
                                $paxList[] = [
                                    "first_name" => $request->paxfname,
                                    "last_name" => $request->paxlname,
                                ];
                            }
                            
                        }

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/finish/', [
                "user" => [
                        "email" => $request->email, 
                        "comment" => $request->comment, 
                        "phone" => $request->phone 
                    ], 
                // "supplier_data" => [
                //             "first_name_original" => "Petera", 
                //             "last_name_original" => "Collinsa", 
                //             "phone" => "12124567880", 
                //             "email" => "peter.collinsa@example.com" 
                //         ], 
                "partner" => [
                            "partner_order_id" => $data['etoken'], 
                            "comment" => "", 
                            // "amount_sell_b2b2c" => 
                            ], 
                "language" => $language, 
                "rooms" => [
                                [
                                    "guests" => $paxList
                                ] 
                            ], 
                "payment_type" => [
                                    "type" => $data['type'], 
                                    "amount" => $data['amount'], 
                                    "currency_code" => $data['curr'] 
                                ], 
            ]);

        return $response->json();

    }

    public function etg_cancel(Request $request){

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/cancel/', [

                "partner_order_id" => $request->number 
                
            ]);

        return json_decode( $response->body() );

    }
    
    public function getStatus(Request $request){
        // Например, получаем что-то из запроса
        $param = $request->input('param');

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/info/', [

                "partner_order_id" => $request->number 
                
            ]);

        // Логика — получаем данные из базы
        $data = SomeModel::where('field', $param)->get();

        // Возвращаем JSON
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
