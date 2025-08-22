<?php

namespace App\Services\Tourmind;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
// use App\Mail\BookCancelMail;
use App\Mail\BookMail;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\CancellationRule;
use App\Models\Book;
use App\Models\Room;
use App\Models\Rate;

class HotelServices
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    public $hotels, $hotelDetail, $hotelLocalData, $tmid, $token, $result;
    public $roomCount = 1;
    public $pricemin;
    public $pricemax, $coef;
    public $user, $guestsall, $paxfname, $paxlname, $childs_name;
    public $price, $currency, $penaltyPrice, $endDate, $mealid, $bedTypeDesc, $rateName;

    public function __construct()
    {
        // $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');
        $this->coef = config('app.main_coef');
        $this->mealTypesApi = [
                        1 => 'No Breakfast',
                        2 => 'Breakfast',
                        3 => 'Lunch',
                        4 => 'Dinner',
                        5 => 'Lunch and Dinner',
                        6 => 'HalfBoard',
                        7 => 'FullBoard',
                        8 => 'AllInclusive',
                        9 => 'SelfCatering',
                    ];

        // соответствие: meal_id (с сайта) => MealType (в API)
        $this->MealTypeMap = [
            1 => 1, // Room Only         => No Breakfast
            2 => 2, // Bed & Breakfast   => Breakfast
            3 => 6, // Half Board        => HalfBoard
            4 => 7, // Full Board        => FullBoard
            5 => 8, // All Inclusive     => AllInclusive
        ];
        
    }

    public function tmGetHotels(Request $request){

        // tourmind get data hotels
        // get local hotels filtereble
        $expl = explode('-', $request->city);

        $query = Hotel::where('city', $expl[1]);
        $query->where('tourmind_id', '!=', '');

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
            ->mapWithKeys(fn($hotel) => [$hotel->tourmind_id => $hotel])
            ->toArray();



            // get tm local ids
        $hoteles = Hotel::where('city', $expl[1]);

        if ($request->rating){
            $hoteles->where('rating', '=', (int)$request->rating);   
        }
        // if ($this->early_in){
        //     $hoteles->where('early_in', $this->early_in);   
        // }
        // if ($this->early_out){
        //     $hoteles->where('early_out', '>=', $this->early_out);   
        // }

        $this->hotels = $hoteles->pluck('tourmind_id')->toArray();
        
        $this->hotelDetail = $this->getHotelDetail($request);

        // dd($this->hotelDetail);

        if( isset($this->hotelDetail['Hotels']) ){

            // merge array local to api 
            foreach ($this->hotelDetail['Hotels'] as &$hotele) {
                $hotelCode = $hotele['HotelCode'];
            
                if (isset($this->hotelLocalData[$hotelCode])) {
                    // Объединяем данные
                    $hotele['localData'] = $this->hotelLocalData[$hotelCode];
                } else {
                    // Если нет локальных данных, добавляем null
                    $hotele['localData'] = null;
                }
            }
            unset($hotele); // Разрываем ссылку, чтобы избежать проблем
            



            // orderby prices, food, cancelled
            $this->hotelDetail['Hotels'] = array_values(array_filter(array_map(function ($hoteli) use ($request) {
                if (empty($hoteli['RoomTypes'])) {
                    return null;
                }

                $filteredRoomTypes = [];
                $lowestRate = null;
                $lowestRateRoomType = null;

                foreach ($hoteli['RoomTypes'] as $roomType) {
                    // Фильтрация тарифов
                    $roomType['RateInfos'] = array_filter($roomType['RateInfos'], function ($rateInfo) use ($request) {
                        $price = (float) $rateInfo['TotalPrice'];
                        $minPrice = $this->pricemin != null ? (float) $this->pricemin : null;
                        $maxPrice = $this->pricemax != null ? (float) $this->pricemax : null;

                        if ($minPrice !== null && $price < $minPrice) {
                            return false;
                        }
                        if ($maxPrice !== null && $price > $maxPrice) {
                            return false;
                        }

                        // Фильтр по питанию
                        if (!empty($request->meal_id)) {
                            $expectedMealType = $this->MealTypeMap[$request->meal_id] ?? null;

                            if (
                                !$expectedMealType ||
                                !isset($rateInfo['MealInfo']['MealType']) ||
                                (int)$rateInfo['MealInfo']['MealType'] !== (int)$expectedMealType
                            ) {
                                return false;
                            }
                        }

                        return true;
                    });

                    // Оставляем только номера, у которых остались тарифы
                    if (!empty($roomType['RateInfos'])) {
                        $filteredRoomTypes[] = $roomType;

                        // Ищем минимальную цену
                        $lowestRateInRoom = min(array_column($roomType['RateInfos'], 'TotalPrice'));

                        if ($lowestRate === null || $lowestRateInRoom < $lowestRate) {
                            $lowestRate = $lowestRateInRoom;
                            $lowestRateRoomType = $roomType;
                        }
                    }
                }

                if (empty($filteredRoomTypes) || $lowestRateRoomType === null) {
                    return null; // Исключаем отель
                }

                // Сохраняем только один RoomType с минимальной ценой
                $hoteli['RoomTypes'] = [$lowestRateRoomType];
                return $hoteli;
            }, $this->hotelDetail['Hotels'])));

            
            // Фильтруем массив отелей, удаляя пустые элементы
            $this->hotelDetail['Hotels'] = array_values(array_filter($this->hotelDetail['Hotels']));
            
            // Log::channel('tourmind')->info('DEbUG - ', $this->hotelDetail);
            // dd($this->hotelDetail);
            
            

            // Оставляем только самый дешевый тариф в каждом отеле
            foreach ($this->hotelDetail['Hotels'] as &$hotel) {
                foreach ($hotel['RoomTypes'] as &$roomType) {
                    if (!empty($roomType['RateInfos'])) {
                        // Сортируем тарифы внутри номера по цене и оставляем только один (самый дешевый)
                        usort($roomType['RateInfos'], fn($a, $b) => (float) $a['TotalPrice'] <=> (float) $b['TotalPrice']);
                        $roomType['RateInfos'] = [reset($roomType['RateInfos'])]; // Берём первый элемент (самый дешевый)
                    }
                }
            }
            unset($hotel, $roomType); // Чистим ссылки для избежания багов



            // Сортируем отели по самому низкому тарифу
            usort($this->hotelDetail['Hotels'], function ($a, $b) {
                // Получаем минимальный тариф для каждого отеля
                $minPriceA = null;
                foreach ($a['RoomTypes'] as $roomType) {
                    if (!empty($roomType['RateInfos'])) {
                        $minPriceA = min(array_column($roomType['RateInfos'], 'TotalPrice'));
                        break; // Останавливаемся, как только нашли хотя бы один тариф
                    }
                }

                $minPriceB = null;
                foreach ($b['RoomTypes'] as $roomType) {
                    if (!empty($roomType['RateInfos'])) {
                        $minPriceB = min(array_column($roomType['RateInfos'], 'TotalPrice'));
                        break;
                    }
                }

                // Если у одного из отелей нет тарифов, он будет позже в списке
                if ($minPriceA === null) return 1;
                if ($minPriceB === null) return -1;

                return $minPriceA <=> $minPriceB; // Сортировка по возрастанию
            });

        }

        return $this->hotelDetail;
        // dd($this->hotelDetail);
        // dd($this->hotelLocalData);
    }

    public function getHotelDetail(Request $request)
    {
        
            // RequestHeader (заголовки запроса)
            $requestHeader = [
                    "AgentCode" => $this->tm_agent_code,
                    "Password" => $this->tm_password,
                    "UserName" => $this->tm_user_name,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ];

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "CheckIn" => $request->arrivalDate,
                "CheckOut" => $request->departureDate,
                "HotelCodes" => $this->hotels,
                "IsDailyPrice" => false,
                //"Nationality" => $this->citizen ?? "EN",
            ];
            
            // 2. Извлекаем массив комнат и сразу считаем общее число взрослых и массив возрастов детей
            $rooms = $request->input('rooms', []); // если нет — пустой массив
            $totalAdults    = 0;
            $allChildAges   = [];
            $childs = 0;
            $roomCount=0;
            foreach ($rooms as $room) {
                $roomCount++;
                // Взрослые
                $totalAdults += (int) ($room['adults'] ?? 0);

                // Возрасты детей (если есть) собираем в единый массив
                if (!empty($room['childAges']) && is_array($room['childAges'])) {
                    foreach ($room['childAges'] as $age) {
                        $allChildAges[] = (int) $age;
                        $childs++;
                    }
                }
            }

            // PaxRooms (информация о размещении гостей)
            $paxRooms = [
                    [
                        "Adults" => (int)$totalAdults,
                        "RoomCount" => (int)$roomCount,
                    ]
                ];
                
                if ( !empty($childs) && !empty($allChildAges ) ) {
                    $paxRooms[0]["Children"] = (int) $childs;
                    $paxRooms[0]["ChildrenAges"] = array_values(array_map('intval', $allChildAges));
                }
                

            // Объединение всех частей в один массив
            $payload = array_merge($mainParams, [
                "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
                "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
            ]);
            // dd($payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/HotelDetail", $payload);

            if ($response->failed()) {
                // $this->bookingSuccess = 'Result HotelDetail Ошибка при запросе к API';
                session()->flash('error', 'Result TM HotelDetail Ошибка при запросе к API');
            }
    
            if ( isset($response['Error']['ErrorMessage']) ){
                // $this->bookingSuccess = $response['Error']['ErrorMessage'];
                session()->flash('error', $response['Error']['ErrorMessage']);
            }
            
            // dd($response->json());
            // $this->bookingSuccess .= print_r($payload, 1);

            return $response->json();

        } catch (\Throwable $th) {
            // $this->bookingSuccess = "Ошибка при запросе к API или недоступен Hotel result hotelDetail";
            session()->flash('error', 'Ошибка при запросе к API TM');
        }
    }
    
    public function getOneDetail(Request $request, $hotelid)
    {

            $query = Hotel::where('id', $hotelid)->get('tourmind_id')->first();
            $id = $query->tourmind_id;

            // RequestHeader (заголовки запроса)
            $requestHeader = [
                    "AgentCode" => $this->tm_agent_code,
                    "Password" => $this->tm_password,
                    "UserName" => $this->tm_user_name,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ];

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "CheckIn" => $request->arrivalDate,
                "CheckOut" => $request->departureDate,
                "HotelCodes" => [(int) $id],
                "IsDailyPrice" => false,
                //"Nationality" => $this->citizen ?? "EN",
            ];

            // PaxRooms (информация о размещении гостей)
            $paxRooms = [
                    [
                        "Adults" => (int)$request->adult,
                        "RoomCount" => (int)$this->roomCount,
                    ]
                ];

                if ( !empty($request->child) && !empty($request->childAges ) ) {
                    $paxRooms[0]["Children"] = (int) $request->child;
                    $paxRooms[0]["ChildrenAges"] = array_values(array_map('intval', $request->childAges));
                }
                

            // Объединение всех частей в один массив
            $payload = array_merge($mainParams, [
                "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
                "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
            ]);
            // dd($payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/HotelDetail", $payload);

            if ($response->failed()) {
                // $this->bookingSuccess = 'Result HotelDetail Ошибка при запросе к API';
                session()->flash('error', 'Result TM HotelDetail Ошибка при запросе к API');
            }
    
            if ( isset($response['Error']['ErrorMessage']) ){
                // $this->bookingSuccess = $response['Error']['ErrorMessage'];
                session()->flash('error', $response['Error']['ErrorMessage']);
            }
            
            // dd($response);
            // $this->bookingSuccess .= print_r($payload, 1);

            return json_decode($response->body());

        } catch (\Throwable $th) {
            // $this->bookingSuccess = "Ошибка при запросе к API или недоступен Hotel result hotelDetail";
            session()->flash('error', 'Ошибка при запросе к API TM');
        }
    }

    public function getOneDetailForCalendar(Request $request, $hotel)
    {

            // $query = Hotel::where('id', $hotelid)->get('tourmind_id')->first();
            $id = $hotel->tourmind_id;

            // RequestHeader (заголовки запроса)
            $requestHeader = [
                    "AgentCode" => $this->tm_agent_code,
                    "Password" => $this->tm_password,
                    "UserName" => $this->tm_user_name,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ];

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "CheckIn" => date('Y-m-10'),
                "CheckOut" => date('Y-m-d', strtotime('+1 months')),
                "HotelCodes" => [(int) $id],
                "IsDailyPrice" => false,
                //"Nationality" => $this->citizen ?? "EN",
            ];

            // PaxRooms (информация о размещении гостей)
            $paxRooms = [
                    [
                        "Adults" => 1,
                        "RoomCount" => 1,
                    ]
                ];

            
            // Объединение всех частей в один массив
            $payload = array_merge($mainParams, [
                "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
                "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
            ]);
            // dd($payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/HotelDetail", $payload);

            if ($response->failed()) {
                // $this->bookingSuccess = 'Result HotelDetail Ошибка при запросе к API';
                session()->flash('error', 'Result TM HotelDetail Ошибка при запросе к API');
            }
    
            if ( isset($response['Error']['ErrorMessage']) ){
                // $this->bookingSuccess = $response['Error']['ErrorMessage'];
                session()->flash('error', $response['Error']['ErrorMessage']);
            }
            
            // dd($response);
            // $this->bookingSuccess .= print_r($payload, 1);

            return json_decode($response->body());

        } catch (\Throwable $th) {
            // $this->bookingSuccess = "Ошибка при запросе к API или недоступен Hotel result hotelDetail";
            session()->flash('error', 'Ошибка при запросе к API TM');
        }
    }

    public function checkRoomRate(Request $request){

        $tmid = Hotel::where('id', $request->hotel_id)->get('tourmind_id')->first();
        $this->tmid = $tmid->tourmind_id;

        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => $this->tm_agent_code,
                "Password" => $this->tm_password,
                "UserName" => $this->tm_user_name,
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

        // Основные параметры запроса (без заголовков и PaxRooms)
        $mainParams = [
            "CheckIn" => $request->arrivalDate,
            "CheckOut" => $request->departureDate,
            "HotelCodes" => [(int) $tmid->tourmind_id ?? 0],
            "RateCode" => $request->rate_code,
            "Nationality" => $request->citizen ?? "EN",
            "IsDailyPrice" => false,
        ];

        // PaxRooms (информация о размещении гостей)
        $paxRooms = [
                [
                    "Adults" => (int) $request->adult,
                    "RoomCount" => $this->roomCount
                ]
            ];

            if ( !empty($request->child) && !empty($request->childAges ) ) {
                $paxRooms[0]["Children"] = (int) $request->child;
                $paxRooms[0]["ChildrenAges"] = array_values(array_map('intval', $request->childAges));
            }
            

        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
        ]);

        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/CheckRoomRate", $payload);

        // if ($response->failed()) {
        //     return ['Error' => ["ErrorMessage" => 'HotelDetail Ошибка при запросе к API']];
        // }

        $data = json_decode($response->body());
        // return $payload;
            // dd(json_encode($payload, true));
            // dd($payload);

        return $data;
    }
    
    public function createOrder(Request $request){

        $check = $this->checkRoomRate($request);
        //dd($check);
        $mrate = '';
        if ( isset($check->Hotels[0]->RoomTypes[0]->RateInfos[0]->CancelPolicyInfos[0]->Amount) ){
            $mrate = $check->Hotels[0]->RoomTypes[0]->RateInfos[0];
            $this->price = $mrate->TotalPrice;
            $this->currency = $mrate->CurrencyCode ?? 'CNY';
            $this->mealid = $mrate->MealInfo->MealType ?? '1';
            $this->rateName = $mrate->Name ?? '';
            $this->penaltyPrice = number_format(($mrate->CancelPolicyInfos[0]->Amount / $this->coef), 2, '.', '') ?? 0;
            $this->endDate = $mrate->CancelPolicyInfos[0]->From ?? null;
            $this->bedTypeDesc = $mrate->bedTypeDesc;
        }else{
            // dd($check);
            $mrate = $check->Hotels[0]->RoomTypes[0]->RateInfos[0];
            $this->price = $request->price;
            $this->currency = $request->currency ?? 'CNY';
            $this->mealid = $request->mealid ?? '1';
            $this->penaltyPrice = number_format(($mrate->TotalPrice / $this->coef), 2, '.', '') ?? 0;
            $this->endDate = $request->cancelDate ?? null;
            $this->rateName = $request->rate_name ?? '';
            $this->bedTypeDesc = $request->rate_name;
        }

                $basePrice = (float) $request->sum;
                $toCurrency = strtoupper($request->source_sym ?? 'USD');

                $converted = app(\App\Services\FXService::class)->convert($basePrice, $this->currency, $request->source_sym);
                $symbol = $toCurrency;

                $cancelConverted = app(\App\Services\FXService::class)->convert((float) $this->penaltyPrice, $this->currency, $toCurrency);
                $cancelSymbol = $toCurrency;
        
        // dd($mrate);

            $userId = Auth::id();
            $this->user = auth()->user();
            $agentid = '';
            $this->token = $request->token;

                if ( empty($this->token) ){
                    do {
                        $this->token = Str::random(40);
                    } while (Book::where('book_token', $this->token)->exists());
                }

                do {
                    $agentid = "SWT-" . now()->format('Ymd') . '-' . Str::uuid();
                } while (Book::where('agent_ref', $agentid)->exists());

                


        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => $this->tm_agent_code,
                "Password" => $this->tm_password,
                "UserName" => $this->tm_user_name,
                "TransactionID" => $this->token,
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "AgentRefID" => $agentid,
                "CheckIn" => $request->arrivalDate,
                "CheckOut" =>  $request->departureDate,
                "HotelCode" => (int) $this->tmid,
                "RateCode" => $request->rate_code,
                "SpecialRequest" => $request->comment,
                "CurrencyCode" => $this->currency, // CNY
                "TotalPrice" => (float) $this->price,
            ];
            // "Nationality" => $citizen,

                // PaxRooms (информация о размещении гостей)
                $paxRooms = [
                        [
                            "Adults" => (int) $request->adult,
                            "RoomCount" => $this->roomCount,
                        ]
                    ];

                    if ( !empty($request->child) && !empty($request->childAges ) ) {
                        $paxRooms[0]["Children"] = (int) $request->child;
                        $paxRooms[0]["ChildrenAges"] = array_values(array_map('intval', $request->childAges));
                    }
                
                    $paxList = [];

                    for ($i = 0; $i < $this->roomCount; $i++) {
                        $j = $i + 1;

                        if($j > 1){

                            $paxList[] = [
                                "FirstName" => $request->{'paxfname' . $j},
                                "LastName" => $request->{'paxlname' . $j},
                                "Type" => "ADU",
                            ];
                                
                        }else{
                            
                            $paxList[] = [
                                "FirstName" => $request->paxfname,
                                "LastName" => $request->paxlname,
                                "Type" => "ADU",
                            ];
                        }
                        
                    }

                    $paxRooms[0]["PaxNames"] = $paxList;

                $phone = (int) $this->user->phone;
                $ContactInfo = [
                        "Email" => $this->user->email,
                        "FirstName" => $this->user->name,
                        "LastName" => $this->user->lastname,
                        "PhoneNo" => (string) $phone,
                ];

                    // Объединение всех частей в один массив
                    $payload = array_merge($mainParams, [
                        "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
                        "RequestHeader" => $requestHeader,  // Просто вставляем массив RequestHeader
                        "ContactInfo" => $ContactInfo
                    ]);

        
            // dd($payload);
            // die;
            
            // local create order

        $existbook = Book::where('book_token', $this->token)->first();

        if ( $existbook ){
            return ['Success' => 'Этот бронь уже существует!'];
        }


            $room = Room::where('tourmind_id', $request->RoomTypeCode)->first();
            
            if ( empty($room) ){

                $room = Room::updateOrCreate(
                    [
                        'hotel_id' => (int)$request->hotel_id,
                        'tourmind_id' => $request->RoomTypeCode,
                    ],
                    [
                        'title' => $request->room_name,
                        'title_en' => $request->room_name,
                        'description_en' => $request->rate_name
                    ],
                    
                );
                
            }

                for ($i = 0; $i <= $request->adult; $i++) {
                    $fname = $request->input('paxfname' . $i);
                    // $lname = $request->input('paxlname' . ($i > 1 ? $i : ''));
                        
                    $fullName = trim($fname);
                    
                    if ($fullName) {
                        $this->guestsall[] = $fullName;
                    }
                }
                
                for ($i = 0; $i <= $request->child; $i++) {
                    $fio = $request->input('child_name' . $i);
                    // $lname = $request->input('paxlname' . ($i > 1 ? $i : ''));
                        
                    $fullName = trim($fio);
                    
                    if ($fullName) {
                        $this->childs_name[] = $fullName;
                    }
                }
                
                    $guests = implode(',', $this->guestsall ?? []);
                    $childsName = implode(',', $this->childs_name ?? []);
                    $childAges = implode(',', $request->childAges ?? []);

                    // $offset = str_replace('UTC', '', $this->utc); // '+3'

                    // Преобразуем в +03:00
                    // $formattedOffset = sprintf('%+03d:00', (int)$offset);

                    // Получаем текущую дату/время в нужной зоне
                    $utcdatetime = Carbon::now($request->utc);

                    // Форматируем результат
                    $utcdatetime = $utcdatetime->format('Y-m-d H:i:s');

                
                

                    
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
                            "penalty_amount" => $cancelConverted, //$this->penaltyPrice ?? 0,
                            "end_date" => $this->endDate ?? null,
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
                            "penalty_amount" => $cancelConverted, //$this->penaltyPrice ?? 0,
                            "end_date" => null,
                            "description" => '',
                            "hotel_id" => $request->hotel_id,
                        ]
                    );

                    $ruleid = $rule->id ?? null;
                }


                    $totalPrice = number_format(($this->price / $this->coef),2 ,'.', '');
                    
                    $rate = Rate::UpdateOrCreate(
                        [
                            'rate_code' => $request->rate_code,
                            'hotel_id' => $request->hotel_id,
                            'room_id' => $room->id,
                        ],
                        [
                            'title' => $mrate->Name ?? '',
                            'title_en' => $mrate->Name ?? '',
                            'desc_en' => null,
                            'bed_type' => $this->bedTypeDesc ?? $request->rate_name,
                            'meal_id' => $this->mealid,
                            'allotment' => null,
                            'adult' => $request->adult ?? 1,
                            'child' => $request->child ?? 0,
                            'children_allowed' => 0,
                            'free_children_age' => 0,
                            'currency' => $symbol, //$this->currency,
                            'price' => $this->price,
                            'price2' => null,
                            'child_extra_fee' => 0,
                            'availability' => 0,
                            'total_price' => $converted, //$totalPrice,
                            'cancellation_rule_id' => $ruleid ?? null,
                            
                        ]
                    );
                    
                    $book = Book::firstOrCreate(
                        [
                            'book_token' => $this->token,
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
                            'adult' => $request->adult,
                            'child' => $request->child,
                            'childages' => $childAges ?? '',
                            'price' => $this->price,
                            'source_sym' => $this->currency ?? $request->currency ?? 'CNY',
                            'sum' => $converted,
                            'utc' => $request->utc,
                            'cancellation_id' => $ruleid,
                            'cancel_penalty' => $cancelConverted,
                            'currency' => $symbol,
                            'cancel_date' => $utcdatetime,
                            'arrivalDate' => $request->arrivalDate,
                            'departureDate' => $request->departureDate,
                            'status' => 'Pending',
                            'user_id' => $userId,
                            'api_type' => 'tourmind',
                            'agent_ref' => $agentid,
                        ]
                    );

                    if ( !isset($book->id) ){
                        return ['Error' => true, 'ErrorMessage' => 'Ошибка при создании брони! Пожалуйста, попробуйте позже!'];
                    }


            // tourmind create order
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/CreateOrder", $payload);
                
            if ( $response->failed() ) {
    
                Log::channel('tourmind')->error('521 CreateOrder - ', $payload);
                Log::channel('tourmind')->error('522 CreateOrder - ', $response);
    
                // return "522 Book order Ошибка при запросе к API Tourmind! Попробуйте через несколько секунд!";
                return ['Error' => true, 'ErrorMessage' => 'Ошибка при создании брони! Пожалуйста, попробуйте позже!'];
            }
    
    
            $order = $response->json();
            
            Log::channel('tourmind')->info('CreateOrder 528 - ', $payload);
            Log::channel('tourmind')->info('CreateOrder 528 - ', $order);
            
            if( isset($order['OrderInfo']['OrderStatus']) == 'CONFIRMED' ){
    
                $message = $order['OrderInfo']['OrderStatus'];

                    $status;
                    match ($order['OrderInfo']['OrderStatus']) {
                        'CONFIRMED' => $status = 'Confirmed',
                        'PENDING' => $status = 'Pending',       
                        'FAILED' => $status = 'Failed',
                        'CANCELLED' => $status = 'Cancelled',
                        default => $status = '',
                    };


                    $book = Book::where('book_token', $this->token)->first();

                    if ($book) {
                        $book->status = 'Confirmed';
                        // $book->rezervation_id = $order['OrderInfo']['ReservationID'] ?? null;
                        $book->save();

                        $userEmail = Auth::user()->email;
                        Mail::to($userEmail)->send(new BookMail($book));
                    }
                        

                return ['Success' => "{$order['OrderInfo']['OrderStatus']}"];
    
            }elseif($order['OrderInfo']['OrderStatus'] != 'FAILED'){

                return ['Error' => "{$order['OrderInfo']['OrderStatus']}"];
                
            }else{
    
                return ['Error' => true, 'ErrorMessage' => $order['Error']['ErrorMessage']];
    
            }

        // return $payload;
            
    }

    public function cancelOrder(Request $request, $book){

        // cancel order from tourmind
        
       try {
        
            $agent = $book->agent_ref;
            $token = $book->book_token;

                $payload = [
                    "AgentRefID" => $agent,
                    "RequestHeader" => [
                        "AgentCode" => $this->tm_agent_code,
                        "Password" => $this->tm_password,
                        "UserName" => $this->tm_user_name,
                        "TransactionID" => $token,
                        "RequestTime" => now()->format('Y-m-d H:i:s')
                    ]
                ];
            
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ])->post("{$this->baseUrl}/CancelOrder", $payload);

            $data = $response->json();

            return $data;
            

        } catch (\Throwable $th) {
                return ["Error" => "TM CancelOrder Ошибка при запросе к API: " . $th->getMessage()];
                // throw new \Exception("TM CancelOrder Ошибка при запросе к API: " . $th->getMessage(), 0, $th);
           }
        
    }

    public function getOneSearchOrder($AgentRefID){
            
           try {
                $payload = [
                    "AgentRefID" => $AgentRefID,
                    "RequestHeader" => [
                        "AgentCode" => $this->tm_agent_code,
                        "Password" => $this->tm_password,
                        "UserName" => $this->tm_user_name,
                        "RequestTime" => now()->format('Y-m-d H:i:s')
                    ]
                ];
            
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->post("{$this->baseUrl}/SearchOrder", $payload);

                if ($response->failed()) {
                    return ['Error' => "TM SearchOrder Ошибка при запросе к API"];
                }

                return $response->json();

            } catch (\Throwable $th) {
                
                Log::channel('tourmind')->info('CreateOrder 946 - ', $response->json());
                return ["Error" => "TM SearchOrder Ошибка при запросе к API: " . $th->getMessage()];
                
            }
    }

    public function updateBookingStatuses(){

        $books = Book::where('api_type', 'tourmind')
            ->whereIn('status', ['Pending', 'Failed'])
            ->get('id','book_token','agent_ref');

        foreach ($books as $book) {

            $order = $this->getOneSearchOrder($book->agent_ref);

            if( isset($order['OrderInfo']['OrderStatus']) ){

                $status = $order['OrderInfo']['OrderStatus'];

                Book::where('book_token', $book->book_token)
                    ->update(['status' => ucfirst( $order['OrderInfo']['OrderStatus'] )]);

                    echo "Done {$book->id} - {$status} \n";
            }
        }
        
    }

    public function getUtcOffsetByCountryCode($CountryCode){
        // ISO 3166-1 alpha-2
        $tzList = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $CountryCode);

        $timezone = new DateTimeZone($tzList[0]);
        $now = new DateTime('now', $timezone);
        $offsetInSeconds = $timezone->getOffset($now);
        $offsetFormatted = sprintf('%s%02d:%02d',
            $offsetInSeconds < 0 ? '-' : '+',
            abs($offsetInSeconds) / 3600,
            (abs($offsetInSeconds) % 3600) / 60
        );

        return $offsetFormatted;
    }

    function getUtcOffsetByCityName($city)
    {
        $cityName = strtolower($city);
        $foundTimezone = null;
    
        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            if (strtolower(substr($timezone, strrpos($timezone, '/') + 1)) == $cityName) {
                $foundTimezone = $timezone;
                break;
            }
        }
    
        if ($foundTimezone) {
            $tz = new DateTimeZone($foundTimezone);
            $now = new DateTime('now', $tz);
            $offset = $tz->getOffset($now) / 3600;
    
            return 'UTC' . ($offset >= 0 ? '+' : '') . $offset;
        }
    
        return 'Timezone not found';
    }
}