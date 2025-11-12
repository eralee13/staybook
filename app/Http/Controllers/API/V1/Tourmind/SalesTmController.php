<?php

namespace App\Http\Controllers\API\V1\Tourmind;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;
use App\Mail\BookCancelMail;
use App\Mail\BookMail;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\CancellationRule;
use App\Models\Book;
use App\Models\Room;
use App\Models\Rate;

class SalesTmController extends Controller
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    public $hotels, $hotelDetail, $hotelLocalData, $tmid, $token, $result;
    public $roomCount = 1;
    public $pricemin;
    public $pricemax, $coef;
    public $user, $guestsall, $paxfname, $paxlname, $childs_name;
    public $price, $currency, $penaltyPrice, $endDate, $mealid, $bedTypeDesc, $rateName;

    public function __construct(Request $request)
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
            3 => 3, // Half Board        => HalfBoard
            4 => 4, // Full Board        => FullBoard
            5 => 5, // All Inclusive     => AllInclusive
        ];

    }

    // Получение детальной информации об отелях и тарифов из Tourmind
    public function searchHotels(Request $request)
    {
        // request sample for staybook
        // {
        //     "hotel_ids": "889,883",
        //     "city": "Kyiv",
        //     "check_in": "2025-10-29",
        //     "check_out": "2025-10-30",
        //     "residency": "ua",
        //     "guests_groups": [
        //         {
        //             "adults": 1,
        //             "child": 0,
        //             "childages": [0]
        //         }
        //     ],
        //     "transaction_id": "1234567890"
        // }

        // request sample for tm
        // {
            //     "CheckIn": "2018-08-25",
            //     "CheckOut": "2018-08-26",
            //     "HotelCodes": [12345],
            //     "IsDailyPrice": false,
            //     "Nationality": "CN",
            //     "PaxRooms": [
            //         {
            //         "Adults": 1,
            //         "Children": 1, // optional
            //         "ChildrenAges": [6], // optional
            //         "RoomCount": 2
            //         }
            //     ],
            //     "Timeout": 60000
            // }
        
            // return response()->json(['success' => true, 'data' => $request->hotel_ids]);

        if( !empty( $request->hotel_ids ) ){

            $ids = explode(',', $request->hotel_ids);

            $this->hotels = Hotel::whereIn('id', $ids)
                ->whereNotNull('tourmind_id')
                ->pluck('tourmind_id')
                ->toArray();

        }else{

            $this->hotels = Hotel::where('city', $request->city)
                ->whereNotNull('tourmind_id')
                ->pluck('tourmind_id')
                ->toArray();

        }
        
        

        // PaxRooms (информация о размещении гостей)
        // Получаем JSON из запроса
        $groups = collect($request->input('guests_groups'));

            $paxRooms = $groups->map(function ($group) {
                $room = [
                    "Adults" => (int) $group['adults'],
                    "RoomCount" => 1,
                ];

                // Добавляем детей, если они есть
                if (!empty($group['child']) && $group['child'] > 0) {
                    $room["Children"] = (int) $group['child'];
                    $room["ChildrenAges"] = $group['childages'] ?? [];
                }

                return $room;
            })->values()->toArray();

                // Общая статистика
                $totalAdults = $groups->sum('adults');
                $totalChildren = $groups->sum('child');
                $totalRooms = $groups->count();
                
                
        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => $this->tm_agent_code,
                "Password" => $this->tm_password,
                "UserName" => $this->tm_user_name,
                "TransactionID" => $request->transaction_id,
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

        // Основные параметры запроса (без заголовков и PaxRooms)
        $mainParams = [
            "CheckIn" => $request->check_in,
            "CheckOut" => $request->check_out,
            "HotelCodes" => $this->hotels,
            "IsDailyPrice" => true,
            "Nationality" => $request->citizen ?? null,
        ];

        // Объединение всех частей в один массив
        $payload = array_merge($mainParams, [
            "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
            "RequestHeader" => $requestHeader,  // Просто вставляем массив RequestHeader
            "Timeout" => 60000,
        ]);

        // return response()->json(['success' => true, 'data' => $payload]);
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/HotelDetail", $payload);

            // if ( isset($response['Error']['ErrorMessage']) ){
            //     // $this->message = $response['Error']['ErrorMessage'];
            // }
            
            // dump($payload);
            // dump($response->json());

            Log::channel('tourmind_sales')->info('Sales API: Search Hotels - ', $payload);
            Log::channel('tourmind_sales')->info('Sales API: Search Hotels - ', $response->json());

            return $response->json();

        } catch (\Throwable $th) {

            Log::channel('tourmind_sales')->info('Sales API: Search Hotels Catch - ', [$th->getMessage()]);

            return $th->getMessage();
        }
    }

    // Вызывайте этот метод сразу перед бронированием, чтобы актуализировать цену!
    public function actualize(Request $request){

        // checkRoomRate method on tourmind
        // request sample for staybook
            // {
            //     "hotel_code": [11295925],
            //     "check_in": "2025-10-29",
            //     "check_out": "2025-10-30",
            //     "residency": "ua",
            //     "rate_code": "v1978803316318810117_98_1",
            //     "guests_groups": [
            //         {
            //             "adults": 1,
            //             "children": 0, optional
            //             "children_ages": [0] optional
            //         }
            //     ]
            // }

        // $tmid = Hotel::where('id', $request->hotel_id)->get('tourmind_id')->first();
        // $this->tmid = $tmid->tourmind_id;

        // return response()->json(['success' => true, 'data' => $payload]);

        // PaxRooms (информация о размещении гостей)
        // Получаем JSON из запроса
        $groups = collect($request->input('guests_groups'));

            $paxRooms = $groups->map(function ($group) {
                $room = [
                    "Adults" => (int) $group['adults'],
                    "RoomCount" => 1,
                ];

                // Добавляем детей, если они есть
                if (!empty($group['child']) && $group['child'] > 0) {
                    $room["Children"] = (int) $group['child'];
                    $room["ChildrenAges"] = $group['childages'] ?? [];
                }

                return $room;
            })->values()->toArray();

                // Общая статистика
                $totalAdults = $groups->sum('adults');
                $totalChildren = $groups->sum('child');
                $totalRooms = $groups->count();


        // RequestHeader (заголовки запроса)
        $requestHeader = [
                "AgentCode" => $this->tm_agent_code,
                "Password" => $this->tm_password,
                "UserName" => $this->tm_user_name,
                "TransactionID" => $request->transaction_id,
                "RequestTime" => now()->format('Y-m-d H:i:s')
            ];

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "CheckIn" => $request->check_in,
                "CheckOut" => $request->check_out,
                "HotelCodes" => [(int) $request->hotel_code] ?? [],
                "RateCode" => $request->rate_code,
                "Nationality" => $request->citizen ?? null,
                "IsDailyPrice" => true,
            ];

                // Объединение всех частей в один массив
                $payload = array_merge($mainParams, [
                    "PaxRooms" => $paxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
                    "RequestHeader" => $requestHeader  // Просто вставляем массив RequestHeader
                ]);

        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post("{$this->baseUrl}/CheckRoomRate", $payload);


        // dump($payload);
        // dump($response->json());

            Log::channel('tourmind_sales')->info('Sales API: Check Rate - ', $payload);
            Log::channel('tourmind_sales')->info('Sales API: Check Rate - ', $response->json());

            // return $payload;
        return $response->json();
    }
    
    public function createOrder(Request $request){

        // request sample for staybook
            // {
            //     "hotel_code": 11295925,
            //     "check_in": "2025-10-29",
            //     "check_out": "2025-10-30",
            //     "agent_ref_id": "1564sdsdf2342sfdsdf",
            //     "rate_code": "v1979080841103785984_98_1",
            //     "total_price": 310.85,
            //     "currency": "CNY",
            //     "SpecialRequest": null,
            //     "guests_groups": [
            //         {
            //             "adults": 1,
            //             "children": 0,
            //             "children_ages": [],
            //             "pax_names": [
            //                 {
            //                 "first_name": "Tom",
            //                 "last_name": "Lee",
            //                 "children": false
            //                 }
            //             ]
            //         }
            //     ],
            //     "ContactInfo": {
            //         "Email": "xxx@google.com",
            //         "FirstName": "Tom",
            //         "LastName": "Lee",
            //         "PhoneNo": "1521777778"
            //     }
            // }

        $guests_groups = $request->input('guests_groups'); // или json_decode($request->guests_groups, true)

        $PaxRooms = [];
        $RoomCount = count($guests_groups); // общее количество комнат

        foreach ($guests_groups as $group) {
            $room = [
                "Adults" => (int) $group['adults'],
                "Children" => (int) $group['children'],
                "ChildrenAges" => $group['children_ages'] ?? [],
                "PaxNames" => [],
                "RoomCount" => 1,
            ];

            foreach ($group['pax_names'] as $pax) {
                $room['PaxNames'][] = [
                    "FirstName" => $pax['first_name'],
                    "LastName" => $pax['last_name'],
                    "Type" => $pax['children'] ? 'CHI' : 'ADU',
                ];
            }

            $PaxRooms[] = $room;
        }

            // Основные параметры запроса (без заголовков и PaxRooms)
            $mainParams = [
                "AgentRefID" => $request->agent_ref_id,
                "CheckIn" => $request->check_in,
                "CheckOut" =>  $request->check_out,
                "HotelCode" => (int) $request->hotel_code, // tourmind hotel id
                "RateCode" => $request->rate_code,
                "SpecialRequest" => $request->comment, // special comment
                "CurrencyCode" => $request->currency ?? "CNY", // CNY
                "TotalPrice" => (float) $request->total_price, // Total price on actualize Response.
            ];

                $ContactInfo = [
                        "Email" => $request->email,
                        "FirstName" => $request->firstname,
                        "LastName" => $request->lastname,
                        "PhoneNo" => (string) $request->phone,
                ];

                    // RequestHeader (заголовки запроса)
                    $requestHeader = [
                            "AgentCode" => $this->tm_agent_code,
                            "Password" => $this->tm_password,
                            "UserName" => $this->tm_user_name,
                            "TransactionID" => $request->transaction_id,
                            "RequestTime" => now()->format('Y-m-d H:i:s')
                        ];

            // Объединение всех частей в один массив
            $payload = array_merge($mainParams, [
                "PaxRooms" => $PaxRooms,  // Убеждаемся, что PaxRooms — это массив массивов
                "RequestHeader" => $requestHeader,  // Просто вставляем массив RequestHeader
                "ContactInfo" => $ContactInfo
            ]);


            // tourmind create order
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/CreateOrder", $payload);
                
            Log::channel('tourmind_sales')->info('Sales API: Create Order - ', $payload);
            Log::channel('tourmind_sales')->info('Sales API: Create Order - ', $response->json());

        return $response->json();
            
    }

    // cancel order from tourmind
    public function cancelOrder(Request $request){

        // request sample for staybook 
        // @string AgentRefID
        // @object RequestHeader
        // {
        //     "agent_ref_id": "1234654654654asd654ad56465"
        // }

        try {
    
            $payload = [
                "AgentRefID" => $request->agent_ref_id,
                "RequestHeader" => [
                    "AgentCode" => $this->tm_agent_code,
                    "Password" => $this->tm_password,
                    "UserName" => $this->tm_user_name,
                    // "TransactionID" => $token,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ]
            ];
        
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->post("{$this->baseUrl}/CancelOrder", $payload);

                Log::channel('tourmind_sales')->info('Sales API: Cancel Order - ', $payload);
                Log::channel('tourmind_sales')->info('Sales API: Cancel Order - ', $response->json());

            return $response->json();
            

        } catch (\Throwable $th) {

                Log::channel('tourmind_sales')->info('Sales API: Cancel Order Catch - ', [$th->getMessage()]);

                return $th->getMessage();
           }
        
    }

    public function searchOrder(Request $request){
            
           try {
                $payload = [
                    "AgentRefID" => $request->agent_ref_id,
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

                Log::channel('tourmind_sales')->info('Sales API: Search Order - ', $payload);
                Log::channel('tourmind_sales')->info('Sales API: Search Order - ', $response->json());


                return $response->json();


            } catch (\Throwable $th) {
                
                Log::channel('tourmind_sales')->info('Sales API: Search Order Catch - ', [$th->getMessage()]);

                return $th->getMessage();
                
            }
    }

    public function updateBookStatuses(){

        $books = Book::where('api_type', 'tourmind')
            ->whereIn('status', ['Pending', 'Failed'])
            ->get('book_token','agent_ref');

        foreach ($books as $book) {

            $order = $this->searchOrder($book->agent_ref);

            if( isset($order['OrderInfo']['OrderStatus']) ){

                Book::where('book_token', $book->book_token)
                    ->update(['status' => ucfirst( $order['OrderInfo']['OrderStatus'] )]);

            }
        }
        
    }

}