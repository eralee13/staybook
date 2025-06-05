<?php

namespace App\Http\Controllers\API\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmergingFormController extends Controller
{
    public $keyId, $apiKey, $url;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
    }

    public function searchHotels(Request $request)
    {

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/search/hp/', [
                "checkin" => "2025-10-22",
                "checkout" => "2025-10-25",
                "residency" => "gb",
                "language" => "en",
                "guests" => [
                    [
                    "adults" => 2,
                    "children" => []
                    ]
                ],
                "timeout" => 30,
                "hid" => 8086020,
                "currency" => "EUR"
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
