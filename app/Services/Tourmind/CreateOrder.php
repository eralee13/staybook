<?php

namespace App\Services\Tourmind;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\Tourmind\TmApiService;
use App\Models\Hotel;
use App\Models\Book;


class CreateOrder
{
    protected TmApiService $tmApiService;
    protected string $baseUrl;

    public function __construct(TmApiService $tmApiService)
    {
        $this->tmApiService = $tmApiService;
        $this->baseUrl = $this->tmApiService->getBaseUrl();
    }

    public function getCheckRoomRate(){

            $nationality;

            $payload = [
                "CheckIn" => "2025-06-06",
                "CheckOut" => "2025-06-10",
                "HotelCodes" => [766917],
                "Nationality" => "CN",
                "PaxRooms" => [
                    [
                    "Adults" => 1,
                    "RoomCount" => 1
                    ]
                ],
                "RateCode" => "13461197298",
                "RequestHeader" => [
                    "AgentCode" => "tms_test",
                    "Password" => "tms_test",
                    "UserName" => "tms_test",
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ]
            ];
    
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/CheckRoomRate", $payload);
    
            if ($response->failed()) {
                Log::channel('tourmind')->info('CheckRoomRate Ошибка при запросе к API', $response->status());
                return ['Error' => 'CheckRoomRate Ошибка при запросе к API', 'status' => $response->status()];
            }

            $data = $response->json();
            //print_r($data);
        
            //Log::channel('tourmind')->info('CheckRoomRate', ['order_id' => 123]);

        //return ['message' => 'Данные обновлены', 'count' => count($regions)];
        return $data;
    }

    public function getCreateOrder($request){
        //$input = $request->all();
        $userId = Auth::id();
        $hotelid;
        $checkIn;
        $checkOut;
        $email;
        $firstName;
        $lastName;
        $phone;
        $currency;
        $adult;
        $children;
        $roomCount;
        $ratecode;
        

        $checkRoom = $this->getCheckRoomRate();
        $checkRoomPrice = $checkRoom['Hotels'][0]['RoomTypes'][0]['RateInfos'][0]['TotalPrice'];

        $countryCodes = $this->tmApiService->getCountryCodes();

        // foreach ($countryCodes as $countryCode) {
            
            $payload = [
                "AgentRefID" => "swt[$userId]",
                "CheckIn" => "2025-06-06",
                "CheckOut" => "2025-06-10",
                "ContactInfo" => [
                    "Email" => "xxx@google.com",
                    "FirstName" => "Tom",
                    "LastName" => "Lee",
                    "PhoneNo" => "1521777777"
                ],
                "CurrencyCode" => "CNY",
                "HotelCode" => 766917,
                "PaxRooms" => [
                    [
                    "Adults" => 1,
                    "PaxNames" => [
                            [
                            "FirstName" => "Era",
                            "LastName" => "Lee",
                            "Type" => "ADU"
                            ]
                        ],
                    "RoomCount" => 1
                    ]
                ],
                "RateCode" => "13461197298",
                "SpecialRequest" => "Non-smoking room",
                "TotalPrice" => $checkRoomPrice,
                "RequestHeader" => [
                    "AgentCode" => "tms_test",
                    "Password" => "tms_test",
                    "UserName" => "tms_test",
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ]
            ];
    
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/CreateOrder", $payload);
    
            if ($response->failed()) {
                return ['error' => 'RegionList Ошибка при запросе к API', 'status' => $response->status()];
            }

            $data = $response->json();
            //$regions = $data['RegionListResult']['Regions'] ?? [];
            
            // foreach($regions as $region){

            //     try {
            //         DB::table('cities')->updateOrInsert(
            //             ['country_id' => $region['RegionID']], // Условие проверки
            //             [
            //                 'name' => $region['Name'],
            //                 'country_id' => (int)$region['RegionID'],
            //                 'country_code' => (string)$region['CountryCode'],
            //             ]
            //         );
                    
            //     } catch (Exception $e) {
            //         // Обработка исключения
            //         Log::error('Ошибка: ' . $e->getMessage(), ['exception' => $e]);

            //         // Возвращаем JSON с ошибкой
            //         // return response()->json([
            //         //     'error' => true,
            //         //     'message' => 'Произошла ошибка на сервере',
            //         //     'details' => $e->getMessage() // Можно скрыть в продакшене
            //         // ], 500);
            //     }
            // }

        // }
        Log::channel('tourmind')->info('CreateOrder - ', $data);

        if( !empty($data['OrderInfo']['ReservationID']) ){

            $Book = Book::updateOrCreate(
                [
                    'title' => $firstName.' '.$lastName,
                    'hotel_id' => $hotelid,
                    'phone' => $phone,
                    'email' => $email,
                    'comment' => $comment,
                    'adult' => $adult,
                    'child' => $children,
                    'arrivalDate' => $data['ResponseHeader']['ResponseTime'],
                    'departureDate' => '',
                    'book_token' => $data['ResponseHeader']['TransactionID'],
                    // 'book_token' => $data['OrderInfo']['ReservationID'],
                    'status' => 'В процессе',
                    'uesr_id' => $userId,
                ]
            );

            return ['message' => 'Заказ создан', 'status' => $data['OrderInfo']['OrderStatus']];

        }else{

            return ['error' => $data['Error']['ErrorMessage']];

        }
    }
}