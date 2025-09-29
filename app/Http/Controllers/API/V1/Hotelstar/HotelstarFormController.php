<?php

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
                
                    if (isset($this->hotelLocalData[$hotelCode])) {
                        // Объединяем данные
                        $hotele['localData'] = $this->hotelLocalData[$hotelCode];
                    } else {
                        // Если нет локальных данных, добавляем null
                        $hotele['localData'] = null;
                    }
                }
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
            "currency"    => $fxBase,
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
                "currency"     => $fxBase,
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

                // dump($response->json());

            return $response->json();
                
    }

    public function startOrder(Request $request)
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

        $persons = [];
        // $mappingMeals = $this->mappingMeals();
        $payload = [
                "partner_order_id" => $request->token,
                "email" => $request->email,
                "phone" => $request->phone,
                "persons" => $persons,
                "search_data" => [
                        "hotel_ids" =>$hids,
                        "region_id" => (int)$city[0],
                        "check_in" => $request->arrivalDate,
                        "check_out" => $request->departureDate,
                        "adults" => $adultCount,
                        "children" => $children,
                        "currency" => "RUB",
                        "3d_hotelstar" => null,
                ],
                "search_item" => [
                    "hash" => $request->hash,
                    "provider_id" => $request->provider_id,
                ],
                "meals" => [],
                "extra_fields" => [],
                "client_remarks" => $request->comment,
                "partner_price" => $request->totalPrice,
                "extras" => [],
        ];

        $response = Http::timeout(31)->withHeaders([
                'X-HS-Token' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/book', $payload);

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
                            'total_price' => round($totalPrice),
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


}