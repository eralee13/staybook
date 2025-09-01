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
    public $guestsall, $childs_name;

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

        $city = explode('-', $request->city);

            $response = Http::withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/search/serp/region/', [
                    "checkin" => $request->arrivalDate,
                    "checkout" => $request->departureDate,
                    // "residency" => "uz",
                    // "language" => "en",
                    "guests" => $guests,
                    "timeout" => 30,
                    "region_id" => (int)$city[0],
                    "currency" => "USD"
                ]);
                // dd($response->json());
            return $response->json();
                
    }

    public function searchRates(Request $request)
    {
        // dd($request);
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $guests = [];

        foreach ($rooms as $room) {
            $adultCount = (int) ($room['adults'] ?? 0);

            $children = [];
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $children[] = (int) $age;
                }
            }

            $guests[] = [
                'adults'   => $adultCount,
                'children' => $children,
            ];
        }

        $payload = [
                    "checkin" => $request->arrivalDate,
                    "checkout" => $request->departureDate,
                    // "residency" => "gb",
                    // "language" => "en",
                    "guests" => $guests,
                    // "timeout" => 30,
                    "hid" => (int)$request->apiHotelId,
                    "currency" => "USD"
        ];

        Log::channel('emerging')->info('Booking /search/hp/ - Payload ', $payload);

            $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/search/hp/', $payload);

            return $response->json();

    }

    public function preBook(Request $request){

        $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/prebook/', [

                "hash" => $request->book_hash,
                "price_increase_percent" => (int) $request->increase_percent ?? 0,

            ]);

        // Возвращаем JSON  
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
            
        $mappingMeals = $this->mappingMeals();

        $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/form/', [
                "partner_order_id" => $request->token,
                "book_hash" => $request->book_hash,
                "language" => $language,
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

                for ($i = 0; $i <= $adults; $i++) {
                    $fname = $request->input('paxfname' . $i);
                    // $lname = $request->input('paxlname' . ($i > 1 ? $i : ''));
                    
                    $fullName = trim($fname);

                    if ($fullName) {
                        $this->guestsall[] = trim($fname);
                    }
                }
                
                for ($i = 0; $i <= $childs; $i++) {
                    $fio = $request->input('child_name' . $i);
                    // $lname = $request->input('paxlname' . ($i > 1 ? $i : ''));
                    
                    $fullName = trim($fio);

                    if ($fullName) {
                        $this->childs_name[] = trim($fio);
                    }
                }
                
                    $guests = implode(',', $this->guestsall ?? []);
                    $childsName = implode(',', $this->childs_name ?? []);
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

                    $totalPrice = number_format(($request->price / $this->coef), 2, '.', '');
                    
                    $rate = Rate::Create(
                        [
                            // 'rate_code' => $request->rate_code,
                            'hotel_id' => $request->hotel_id,
                            'room_id' => $room->id,
                            'title' => $request->rate_name ?? '',
                            'title_en' => $request->rate_name ?? '',
                            'desc_en' => null,
                            'bed_type' => $request->bedTypeDesc ?? '',
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
                            'title' => $guests ?? '',
                            'child_name' => $childsName ?? '',
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
                            return ['status' => 'error', 'error' => 'not_created_locally'];
                        }
        }

            // dd( json_decode($response->body()) );
        return $response->json();

    }
    
    public function bookingFinish(Request $request, $data)
    {
        $language = app()->getLocale();
        $rooms = $request->input('rooms', []); // если нет — пустой массив

        $roomsData = [];
        $childIndex = 0;
        $adultIndex = 0;

        foreach ($rooms as $room) {
            $roomGuests = [];

            // Взрослые в этом номере
            $adultCount = (int)($room['adults'] ?? 0);
            for ($i = 0; $i < $adultCount; $i++) {
                $fname = trim($request->input('paxfname' . $adultIndex, ''));
                $parts = preg_split('/\s+/', $fname, 3);

                $lastName  = $parts[0] ?? '';
                $firstName = $parts[1] ?? '';
                $thirdName = $parts[2] ?? '';

                $roomGuests[] = [
                    'first_name' => $firstName,
                    'last_name'  =>trim($lastName . ' ' . $thirdName),
                ];

                $adultIndex++;
            }

            // Дети в этом номере
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $fname = trim($request->input('child_name' . $childIndex, ''));
                    $parts = preg_split('/\s+/', $fname, 3);

                    $lastName  = $parts[0] ?? '';
                    $firstName = $parts[1] ?? '';
                    $thirdName = $parts[2] ?? '';

                    $roomGuests[] = [
                        'first_name' => $firstName,
                        'last_name'  => trim($lastName . ' ' . $thirdName),
                        'age'        => (int)$age,
                    ];

                    $childIndex++;
                }
            }

            $roomsData[] = [
                'guests' => $roomGuests
            ];
        }


        $totalPrice = number_format(($request->price / $this->coef), 2, '.', '');
        // $partnerComment = Auth::user()->partner_comment;

        $payload = [
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
                            // "comment" => $partnerComment ?? '', 
                            "amount_sell_b2b2c" => round($totalPrice)
                            ], 
                "language" => $language, 
                "rooms" => $roomsData, 
                "payment_type" => [
                                    "type" => $data['type'], 
                                    "amount" => $data['amount'], 
                                    "currency_code" => $data['curr'] 
                                ], 
                            ];
                            
                // dd(json_encode($payload));
                Log::channel('emerging')->info('Booking order/booking/finish/ - Payload ', $payload);


        $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/finish/', $payload);
                
        return $response->json();

    }

    public function finishStatus(Request $request){

        $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/finish/status/', [

                "partner_order_id" => $request->token 
                
            ]);

        // Возвращаем JSON
        return $response->json();

    }

    public function etg_cancel(Request $request){

        $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/cancel/', [

                "partner_order_id" => $request->number 
                
            ]);

        return json_decode( $response->body() );

    }

    public function getBookingStatus($token){

        try{

            $response = Http::timeout(30)->withBasicAuth($this->keyId, $this->apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/hotel/order/booking/finish/status/', [
                    "partner_order_id" => $token 
                ]);


            return $response->json();

        } catch (\Throwable $th) {
            
            Log::channel('emerging')->info('Crontab Get Statuses - ', $response->json());
            return ["Error" => "Cron Statuses SearchOrder Ошибка при запросе к API: " . $th->getMessage()];
            
        }

    }

    public function updateBookingStatuses(){

        $books = Book::where('api_type', 'emerging')
            ->where('status', 'Pending')
            ->get('id', 'book_token');

        foreach ($books as $book) {

            $status = $this->getBookingStatus($book->book_token);

            if( $status['status'] == 'ok' ){

                Book::where('book_token', $book->book_token)
                    ->update(['status' => 'Confirmed']);
                echo "Done {$book->id} \n";
            }

            echo "Error - {$status['error']} {$book->id} \n";
        }
        
    }

    public function mappingMeals(){

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
        
        return $mappingMeals;
    }

    public function mappingMealsGrouped(){

        // mapping: id => список ключей
        $mappingMealsGrouped = [
            1 => ['nomeal', 'room-only'],
            2 => [
                'breakfast', 'buffet', 'american-breakfast', 'asian-breakfast',
                'chinese-breakfast', 'continental-breakfast', 'english-breakfast',
                'irish-breakfast', 'israeli-breakfast', 'japanese-breakfast',
                'scandinavian-breakfast', 'scottish-breakfast',
                'breakfast-for-1', 'breakfast-for-2'
            ],
            3 => ['half-board', 'half-board-dinner', 'half-board-lunch', 'some-meal'],
            4 => ['full-board', 'lunch', 'dinner'],
            5 => ['all-inclusive', 'soft-all-inclusive', 'super-all-inclusive', 'ultra-all-inclusive'],
        ];
        
        return $mappingMealsGrouped;
    }

    public function mappingStaybookMeals($mealId)
    {
        $mappingMeals = [
            // 1 => Room Only
            1 => 'nomeal',
            1 => 'room-only',

            // 2 => Bed & Breakfast
            2 => 'breakfast',
            2 => 'buffet',
            2 => 'american-breakfast',
            2 => 'asian-breakfast',
            2 => 'chinese-breakfast',
            2 => 'continental-breakfast',
            2 => 'english-breakfast',
            2 => 'irish-breakfast',
            2 => 'israeli-breakfast',
            2 => 'japanese-breakfast',
            2 => 'scandinavian-breakfast',
            2 => 'scottish-breakfast',
            2 => 'breakfast-for-1',
            2 => 'breakfast-for-2',

            // 3 => Half Board
            3 => 'half-board',
            3 => 'half-board-dinner',
            3 => 'half-board-lunch',
            3 => 'some-meal',

            // 4 => Full Board
            4 => 'full-board',
            4 => 'lunch',
            4 => 'dinner',

            // 5 => All Inclusive
            5 => 'all-inclusive',
            5 => 'soft-all-inclusive',
            5 => 'super-all-inclusive',
            5 => 'ultra-all-inclusive',
        ];

        return $mappingMeals;
    }

}
