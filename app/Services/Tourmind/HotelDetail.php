<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\CategoryRoom;
use App\Models\Rate;
use App\Models\Meal;
use App\Models\Rule;

class HotelDetail
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;
    protected string $tm_agent_code;
    protected string $tm_user_name;
    protected string $tm_password;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = config('app.tm_base_url');
        $this->tm_agent_code = config('app.tm_agent_code');
        $this->tm_user_name = config('app.tm_user_name');
        $this->tm_password = config('app.tm_password');
    }

    public function getHotelDetail(){
        
        //$countryCodes = $this->tmApiService->getCountryCodes();

        // foreach ($countryCodes as $countryCode) {
            
            $tmid = 21110914;
            $payload = [
                "CheckIn" => "2025-06-01",
                "CheckOut" => "2025-06-05",
                "HotelCodes" => [$tmid],
                "IsDailyPrice" => false,
                "Nationality" => "CN",
                "PaxRooms" => [
                    [
                        "Adults" => 1,
                        "Children" => 1,
                        "ChildrenAges" => [5],
                        "RoomCount" => 1
                    ]
                ],
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
            ])->post("{$this->baseUrl}/HotelDetail", $payload);
    
            if ($response->failed()) {
                return ['error' => 'HotelDetail Ошибка при запросе к API', 'status' => $response->status()];
            }

            $data = $response->json();
            $RoomTypes = $data['Hotels'][0]['RoomTypes'] ?? [];
        

            if( !empty($RoomTypes) ){

                $meal = Meal::all('id','title_en');
                $hotel = Hotel::where('tourmind_id', $tmid)->first();
                $hotel_id = $hotel?->id;
                
                foreach($RoomTypes as $RoomType){

                        $RoomTypeCode = $RoomType['RoomTypeCode'];
                        $RoomName = $RoomType['Name'];
                        $RoomDesc = $RoomType['BedTypeDesc'];
                        $RateInfos = $RoomType['RateInfos'];

                        $CategoryRoom = CategoryRoom::updateOrCreate(

                            [
                                'tourmind_id' => $tmid,
                                'type_code' => (int)$RoomTypeCode,
                            ],
                            [
                                'type_code' => (int)$RoomTypeCode,
                                'title_en' => (string)$RoomName,
                                'description_en' => (string)$RoomDesc
                            ],
                            
                        );

                        if( !empty($RateInfos) ){

                            foreach($RateInfos as $RateInfo){

                                $mealVal = collect($meal)->firstWhere('id', (int)$RateInfo['MealInfo']['MealType'])['title_en'];
                               
                                $rate = Rate::updateOrCreate(
                                    [
                                        'hotel_id' => $hotel_id,
                                        'rate_code' => $RateInfo['RateCode']
                                    ],
                                    [
                                        'title_en' => $RateInfo['Name'] ?? '',
                                        'desc_en' => $RateInfo['bedTypeDesc'] ?? '',
                                        'rate_code' => $RateInfo['RateCode'] ?? '',
                                        'allotment' => $RateInfo['Allotment'] ?? '',
                                        'currency' => $RateInfo['CurrencyCode'] ?? '',
                                        'total_price' => $RateInfo['TotalPrice'] ?? '',
                                        'refundable' => $RateInfo['Refundable'] ?? '',
                                        'meal_id' =>  $mealVal ?? '',
                                    ]
                                );

                                if($RateInfo['Refundable'] == true){

                                    Rule::updateOrCreate(
                                        [
                                            'hotel_id' => $hotel_id,
                                            'rate_id' => $rate?->id
                                        ],
                                        [
                                            'start_date_time' => $RateInfo['CancelPolicyInfos'][0]['From'] ?? '',
                                            'end_date_time' => $RateInfo['CancelPolicyInfos'][0]['To'] ?? '',
                                            'amount' => $RateInfo['CancelPolicyInfos'][0]['Amount'] ?? '',
                                            'title' => 'Бесплатная отмена до указанной даты',
                                            'title_en' => 'Free cancellations until the specified date',
                                        ]
                                    );

                                }else{

                                    Rule::updateOrCreate(
                                        [
                                            'hotel_id' => $hotel_id,
                                            'rate_id' => $rate?->id
                                        ],
                                        [
                                            'title' => 'Не может быть отменен',
                                            'title_en' => 'Cannot be canceled.',
                                        ]
                                    );

                                }
                            }
                        }

                    // try {
                    //     DB::table('cities')->updateOrInsert(
                    //         ['country_id' => $region['RegionID']], // Условие проверки
                    //         [
                    //             'name' => $region['Name'],
                    //             'country_id' => (int)$region['RegionID'],
                    //             'country_code' => (string)$region['CountryCode'],
                    //         ]
                    //     );
                        
                    // } catch (Exception $e) {
                    //     // Обработка исключения
                    //     Log::error('Ошибка: ' . $e->getMessage(), ['exception' => $e]);

                    //     // Возвращаем JSON с ошибкой
                    //     // return response()->json([
                    //     //     'error' => true,
                    //     //     'message' => 'Произошла ошибка на сервере',
                    //     //     'details' => $e->getMessage() // Можно скрыть в продакшене
                    //     // ], 500);
                    // }
                }
            }

        // }
           
        return ['message' => 'Данные обновлены', 'count' => count($RoomTypes)];
        // return $data;
    }
}