<?php

namespace App\Http\Controllers\Api\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmergingDescTransHotelController extends Controller
{
    public $keyId, $apiKey, $url;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
    }

    // Method hotel static data
    public function fetchDescTranslationData()
    {

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/static/', [
                'inventory' => 'top',   // можно указать 'current' для актуального
                'language' => 'en',
            ]);

        if ( $response->successful() ) {

            $res = (object) $response->json();
            dd( $res->data );

        } else {

            $res = response()->json([
                'error' => 'Ошибка запроса',
                'status' => $response->status(),
                'details' => $response->json()
            ], $response->status());

            dd($res);
        }
    }
    
}
