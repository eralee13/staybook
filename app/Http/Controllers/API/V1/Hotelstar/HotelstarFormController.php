<?php
    // Описание полей ответа с ошибками
    // code - int - внутренний код ошибки сервиса
        // По-сути ошибки делятся на два блока 500-е и 400-е, коды ошибок только уточняют что конкретно произошло.
        // Актуальный список кодов ошибок:    
        // 50000 // Внутренняя ошибка сервиса, можно попробовать отправить запрос позже
        // 50001 // Опасная ошибка, нужна ручная проверка заказа
        // 50002 // Данные у поставщика не могут быть обновлены
        // 40000 // Невалидный запрос
        // 40001 // Поиск устарел, необходимо перезапустить поиск
        // 40002 // Предложение более недоступно
        // 40003 // Квоты закончились для данного предложения
        // 40004 // Недостаточно средств на депозите, в случае работы с ним
        // 40005 // Партнер попытался забронировать заказ под тем же кодом, что ранее
        // 40006 // Дубль бронирования
        // 40400 - запрашиваемый ресурс не найден
        // 40500 // 405
    // message - string - текст ошибки
    // errors - object - описание ошибок валидации запроса в формате "название поля": "ответ валидатора"
    // 50001 - это когда процесс бронирования на стороне поставщика был запущен, но не завершился успехом и не удалось актуализировать статус брони, в таком случае нет гарантии что на стороне поставщика он не забронировался. При этой ошибке надо тригерить тех. поддержку и разбирать ситуацию вручную, чтобы не оказалось ситуации, когда в системе пусто, а отель ждет клиента.

namespace App\Http\Controllers\API\V1\Hotelstar;

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


class HotelstarFormController extends Controller
{
    public $keyId, $apiKey, $url;
    public $hotelDetail, $hotelLocalData, $hotels;
    public $guestsall, $childs_name;

    public function __construct()
    {
        $this->apiKey = config('app.hotelstar_api_key');
        $this->url = config('app.hotelstar_api_url');
        $this->coef = config('app.main_coef');
    }

    public function HSGetHotels(Request $request)
    {
        // get local hotels by city
        if( is_numeric($request->city) ){
            $query = Hotel::where('hotelstar_id', $request->city);
        }else{
            $expl = explode('-', $request->city);
            $query = Hotel::where('city', $expl[1]);

            $query->where('hotelstar_id', '!=', null);

            if ($request->rating){
                $query->where('rating', '=', (int)$request->rating);   
            }
            // if ($this->early_in){
            //     $query->where('early_in', $this->early_in);   
            // }
            // if ($this->early_out){
            //     $query->where('early_out', '>=', $this->early_out);   
            // }
        }
        
        $query->with(['images', 'amenity']);

        $this->hotelLocalData = $query->get()
            ->mapWithKeys(fn($hotel) => [$hotel->hotelstar_id => $hotel])
            ->toArray();

            // return $this->hotelLocalData;



        //  get hotels by city or id from api 
        $this->hotelDetail = $this->searchHotelsByCityOrId($request);

        // dd($this->hotelDetail);


        if( isset($this->hotelDetail) ){

            usort($this->hotelDetail, function ($a, $b) {
                return $a['price'] <=> $b['price']; // сортировка по возрастанию
            });

            $res = [];
            
            if( is_numeric($request->city) ){

                // merge array local to api 
                $res['rates'] = $this->hotelDetail[0];
                $res['localData'] = $this->hotelLocalData[$request->city] ?? null;
                $this->hotelDetail = [];
                $this->hotelDetail[] = $res;
                
            }else{

                // merge array local to api 
                foreach ($this->hotelDetail as &$hotele) {
                    $hotelCode = $hotele['hotel_id'];
                
                    if ( isset($this->hotelLocalData[$hotelCode]) ) {

                        // Объединяем данные
                        $res['rates'] = $hotele;
                        $res['localData'] = $this->hotelLocalData[$hotelCode];

                    }
                }

                $this->hotelDetail = [];
                $this->hotelDetail[] = $res ? $res : [];

                unset($hotele); // Разрываем ссылку, чтобы избежать проблем
            }
            

            return $this->hotelDetail;
        }

        return $this->hotelDetail;
    }

    public function searchHotelsByCityOrId(Request $request)
    {
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $guests = [];
        // $fxBase = session('currency', 'RUB');

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

        $city = explode('-', $request->city);
        $searchParams = [];

        if (is_numeric($request->city)) {
            $searchParams['hotel_ids'] = [(int)$request->city];
        } else {
            $searchParams['region_id'] = (int)$city[0];
        }

        $payload = array_merge($searchParams, [
            "check_in"    => $request->arrivalDate,
            "check_out"   => $request->departureDate,
            "adults"      => $adultCount,
            "children"    => $children,
            "currency"    => "RUB",
            "3d_hotelstar"=> null,
        ]);
            // dump($payload);
            $response = Http::withHeaders([
                    'X-HS-Token' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/search', $payload);

                dump($response->json());

            return $response->json();
                
    }

    public function searchAsyncHotelsByCityOrId(Request $request, $session)
    {
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $guests = [];
        // $fxBase = session('currency', 'RUB');

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

        $city = explode('-', $request->city);
        $searchParams = [];

        if (is_numeric($request->city)) {
            $searchParams['hotel_ids'] = [(int)$request->city];
        } else {
            $searchParams['region_id'] = (int)$city[0];
        }

        if( empty($session['session']) ){
            $params = '/async_search';
        }else{
            $params = '/async_search/' . $session['hash'] .'?session='. $session['session'];
        }
        

        $payload = array_merge($searchParams, [
            "check_in"    => $request->arrivalDate,
            "check_out"   => $request->departureDate,
            "adults"      => $adultCount,
            "children"    => $children,
            "currency"    => "RUB",
            "3d_hotelstar"=> null,
        ]);
            // dump($payload);

            $response = Http::withHeaders([
                    'X-HS-Token' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . $params, $payload);

                dump($response->json());

            return $response->json();
                
    }

    public function searchActualize(Request $request)
    {
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $guests = [];
        $fxBase = session('currency', 'USD');

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

        $city = explode('-', $request->city);
        $searchParams = [];
        // $searchParams = ['region_id' => 67005]; // Moscow

        if (is_numeric($request->city)) {
            $searchParams['hotel_ids'] = [(int)$request->city];
        } else {
            $searchParams['region_id'] = (int)$city[0];
        }

        $payload = [
            "search_data" => array_merge($searchParams, [
                "check_in"     => $request->arrivalDate,
                "check_out"    => $request->departureDate,
                "adults"       => $adultCount,
                "children"     => $children,
                "currency"     => "RUB",
                "3d_hotelstar" => null,
            ]),
            "search_item" => [
                "hash"        => $request->hash,
                "provider_id" => (int)$request->provider_id,
            ],
        ];

            
            dump($payload);
            $response = Http::timeout(31)->withHeaders([
                    'X-HS-Token' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->url . '/actualize', $payload);

            dump($response->json());

            return $response->json();
                
    }

    public function metaOrder(Request $request)
    {   
        $userId = Auth::id();
        $language = app()->getLocale();
        $rooms = $request->input('rooms', []); // если нет — пустой массив
        $adults = 0;
        $allChildAges = [];
        $childs = 0;
        $roomCount=0;
        $guests = [];
        $fxBase = session('currency', 'USD');
        $early_check_in = $request->early_check_in ?? '';
        $late_check_out = $request->late_check_out ?? '';

        $roomGuests = [];
        $childIndex = 0;
        $adultIndex = 0;

        foreach ($rooms as $room) {
            $roomCount++;
            // Взрослые
            $adults += (int) ($room['adults'] ?? 0);
            $adultse = (int) ($room['adults'] ?? 0);

            // Дети
            $children = [];
            if (!empty($room['childAges']) && is_array($room['childAges'])) {
                foreach ($room['childAges'] as $age) {
                    $children[] = (int) $age;
                    $allChildAges[] = (int) $age;
                    $childs++;
                }
            }

            // Взрослые в этом номере
            $adultCount = (int)($room['adults'] ?? 0);
            for ($i = 0; $i < $adultCount; $i++) {
                $fname = trim($request->input('paxfname' . $adultIndex, ''));
                $parts = preg_split('/\s+/', $fname, 3);

                $lastName  = $parts[0] ?? '';
                $firstName = $parts[1] ?? '';
                $thirdName = $parts[2] ?? '';

                $roomGuests[] = [
                    "name" => $firstName,
                    "surname"  =>trim($lastName . ' ' . $thirdName),
                    // "is_child" => false,
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
                        'name' => $firstName,
                        'surname'  => trim($lastName . ' ' . $thirdName),
                        'age'        => (int)$age,
                        'is_child'   => true,
                    ];

                    $childIndex++;
                }
            }

        }

        $city = explode('-', $request->city);
        $searchParams = [];
        // $searchParams = ['region_id' => 67005]; // Moscow

        if (is_numeric($request->city)) {
            $searchParams['hotel_ids'] = [(int)$request->city];
        } else {
            $searchParams['region_id'] = (int)$city[0];
        }

        $meals = [];
        $meal_reguest = $request->payable_meal;

            if( !empty($meal_reguest) ){
                
                foreach ($meal_reguest as $key => $value) {
                    $meals[] = ['code' => explode('-', $value)[0]];
                }
            }

        
        
        $extras = [];
            if( $early_check_in ){
                $early_check_in = explode('-', $request->early_check_in)[0];

                    $extras[] = [
                        'code' => 'early_check_in',
                        'value' => ['time' => $early_check_in],
                    ];
            }
            if( $late_check_out ){
                $late_check_out = explode('-', $request->late_check_out)[0];

                    $extras[] = [
                        'code' => 'late_check_out',
                        'value' => ['time' => $late_check_out],
                    ];
            }

            $phone = preg_replace('/\D/', '', $request->phone);
        // $mappingMeals = $this->mappingMeals();
        $payload = [
                "partner_order_id" => $request->token,
                "email" => 'itsupport@staybook.asia', //$request->email, Email оформителя заказа
                "phone" => $phone,
                "persons" => $roomGuests,
                "search_data" => array_merge($searchParams, [
                        "check_in" => $request->arrivalDate,
                        "check_out" => $request->departureDate,
                        "adults"       => $adultCount,
                        "children"     => $children,
                        "currency" => "RUB",
                        "3d_hotelstar" => null,
                ]),
                "search_item" => [
                    "hash" => $request->hash,
                    "provider_id" => (int)$request->provider_id,
                ],
                "meals" => $meals,
                "extras" => $extras,
                "client_remarks" => $request->comment,
                "partner_price" => (float)$request->totalPrice,
        ];

        log::channel('hotelstar')->info('Create Order Payload - ', [$payload]);

        $response = Http::timeout(31)->withHeaders([
                'X-HS-Token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/book', $payload);

            $res = json_decode( $response->body() );
            
            dump($payload);
            dump($response->json());
        
            log::channel('hotelstar')->info('Create Order Response - ', [$response->json()]);

            // Проверка на существование локального брони
        $existbook = Book::where('book_token', $request->token)->first();

        if( !$existbook && isset($res->status) && ($res->status == 1 || $res->status == 2) ){

            // Расшифровка статусов:
            //     1 - Новый
            //     2 - Оформлен
            //     3 - Отменен
            //     4 - Отклонен
            //     10 - Ожидает подтверждения бронирования*
            //     20 - Ожидает подтверждения отмены*
            //     30 - Ожидает подтверждения изменений*
            //     500 - Ошибка бронирования

            $room = Room::where('hotelstar_id', $request->room_id)->first();
            
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

                    // $totalPrice = number_format(($request->price / $this->coef), 2, '.', '');
                    
                    $rate = Rate::Create(
                        [
                            // 'rate_code' => $request->rate_code,
                            'hotel_id' => $request->hotel_id,
                            'room_id' => $room->id,
                            'title' => $request->rate_name ?? '',
                            'title_en' => $request->rate_name ?? '',
                            'desc_en' => null,
                            'bed_type' => $room->bedTypeDesc ?? '',
                            'meal_id' => $request->meal_id ?? 1,
                            'allotment' => null,
                            'adult' => $adults ?? 1,
                            'child' => $childs ?? 0,
                            'children_allowed' => 0,
                            'free_children_age' => 0,
                            'price' => $request->price,
                            'price2' => null,
                            'child_extra_fee' => 0,
                            'availability' => 0,
                            'total_price' => $request->totalPrice,
                            'currency' => $request->currency ?? $fxbase,
                            'cancellation_rule_id' => $ruleid ?? null,
                            
                        ]
                    );

                    $book = Book::firstOrCreate(
                        [
                            'book_token' => $request->token,
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
                            'source_sym' => 'RUB',
                            'sum' => $request->totalPrice,
                            'utc' => $request->utc,
                            'cancellation_id' => $ruleid,
                            'cancel_penalty' => $request->cancelPrice,
                            'currency' => $request->currency ?? $fxBase,
                            'cancel_date' => $cancelDate ?? $utcdatetime,
                            'arrivalDate' => $request->arrivalDate,
                            'departureDate' => $request->departureDate,
                            'status' => 'Pending',
                            'early_in' => $early_check_in,
                            'late_out' => $late_check_out,
                            'user_id' => $userId,
                            'api_type' => 'hotelstar',
                            'agent_ref' => $res->order_id ?? null,
                        ]
                    );

                        if ( !isset($book->id) ){
                            return ['status' => 'error', 'error' => 'not_created_locally'];
                        }
        }

        return $res;

    }

    public function metaSearchOrder(Request $request){

        if($request->book_token){
            $payload = ["partner_order_id" => $request->book_token ?? null];
        }else{
            $payload = ["order_id" => $request->order_id ?? null];
        }

        $response = Http::timeout(31)->withHeaders([
                'X-HS-Token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/cancel', $payload);

            $searchResult = json_decode( $response->body() );
            
            dump($payload);
            dump($searchResult);

        return $searchResult;
    }

    public function metaCancelOrder(Request $request, Rate $rate, Book $book){

        if($book->agent_ref){
            $orderId = ["order_id" => $book->agent_ref];
        }else{
            $orderId = ["partner_order_id" => $request->number ?? $book->book_token];
        }

        $payload = array_merge($orderId, [
                "partner_penalty" => (float)$rate->price ?? 0,
        ]);

        $response = Http::timeout(31)->withHeaders([
                'X-HS-Token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/cancel', $payload);

            $cancel = json_decode( $response->body() );
            
            dump($payload);
            dump($cancel);

        return $cancel;
    }

    public function metaCallback(Request $request){
        
        // Вебхук для получения статуса брони
        // Проверим наличие нужных полей
        if (!$request->has('token') || !$request->has('status')) {
            return response()->json(['error' => 'Missing token or status'], 400);
        }

        $token = $request->input('token');
        $status = $request->input('status');

        // Найдём бронь по токену
        $booking = Book::where('partner_order_id', $token)->first();

        if (!$booking) {
            Log::warning("Booking not found for token: {$token}");
            return response()->json(['error' => 'Booking not found'], 404);
        }

        // Обновим статус брони
        $booking->status = $status;
        $booking->save();

        // if( $token && $status){
        //     Book::where('book_token', $token)->update(['status' => 'Reserved']);
        // }

        log::channel('hotelstar')->info('Callback Data - ', [$request->all()]);

        return response()->json(['status' => 'success'], 200);
    }

}