<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; 
use App\Models\Hotel;


class EmergingFormController extends Controller
{
    public $keyId, $apiKey, $url;
    public $hotelDetail, $hotelLocalData, $hotels;
    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
    }

    public function EmergingGetHotels(Request $request)
    {
        // get local hotels by city
        $query = Hotel::where('city', $request->city);
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
                    "region_id" => 378,
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

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/form/', [
                "partner_order_id" => "0b370500-5321-4046-92c5-5982f1a64fc8",
                "book_hash" => "h-b8e4ce1d-fa50-518f-9e22-3effe807a27e",
                "language" => "en",
                "user_ip" => $request->ip(),
            ]);

            return response()->json($response->json());

        // if ( $response->successful() ) {

        //     $res = $response->json();

        //     return response()->json($res);

        // } else {

        //     $res = response()->json([
        //         'error' => 'Ошибка запроса',
        //         'status' => $response->status(),
        //         'details' => $response->json()
        //     ], $response->status());

        //     // dd($res);
        // }
    }
    
    public function booking(Request $request)
    {

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/booking/finish/', [
                "user" => [
                        "email" => "john.smitht@example.com", 
                        "comment" => "The usert comment.", 
                        "phone" => "12244567899" 
                    ], 
                "supplier_data" => [
                            "first_name_original" => "Petera", 
                            "last_name_original" => "Collinsa", 
                            "phone" => "12124567880", 
                            "email" => "peter.collinsa@example.com" 
                        ], 
                "partner" => [
                            "partner_order_id" => "0b370500-5321-4046-92c5-5982f1a64fd8", 
                            "comment" => "The partner comment test.", 
                            "amount_sell_b2b2c" => "10" 
                            ], 
                "language" => "en", 
                "rooms" => [
                                [
                                    "guests" => [
                                        [
                                        "first_name" => "Martinr", 
                                        "last_name" => "Smithe" 
                                        ], 
                                        [
                                            "first_name" => "Eliote", 
                                            "last_name" => "Smitht" 
                                        ] 
                                    ] 
                                ] 
                            ], 
                "upsell_data" => [
                        [
                            "name" => "early_checkin", 
                            "uid" => "d7b56e81-b874-40ee-b195-e2f73d1ec714" 
                        ], 
                        [
                            "name" => "late_checkout", 
                            "uid" => "c4013ea8-3ffd-4eee-bbbc-37693670031e" 
                            ] 
                    ], 
                "payment_type" => [
                                    "type" => "deposit", 
                                    "amount" => "9", 
                                    "currency_code" => "EUR" 
                                ], 
                "return_path" => "https://staybooknew.local/api/v1/EmergingForm" 
            ]);

            return response()->json($response->json());

        // if ( $response->successful() ) {

        //     $res = $response->json();

        //     return response()->json($res);

        // } else {

        //     $res = response()->json([
        //         'error' => 'Ошибка запроса',
        //         'status' => $response->status(),
        //         'details' => $response->json()
        //     ], $response->status());

        //     // dd($res);
        // }
    }
}
