<?php

namespace App\Http\Controllers\Api\V1\Emerging;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmergingAllBookingController extends Controller
{
    
    public $keyId, $apiKey, $url;

    public function __construct()
    {
        $this->keyId = (int) config('app.emerging_key_id');
        $this->apiKey = config('app.emerging_api_key');
        $this->url = config('app.emerging_api_url');
    }

    public function getAll()
    {

        $response = Http::withBasicAuth($this->keyId, $this->apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($this->url . '/hotel/order/info/', [
                'ordering' => [
                    'ordering_type' => 'desc',
                    'ordering_by' => 'created_at'
                ], 
                'pagination' => [
                    'page_size' => 50,
                    'page_number' => 1
                ], 
                'search' => [
                    'created_at' => [
                        'from_date' => '2025-01-01T00:00',
                    ]
                ], 
                'language' => 'en', 
            ]);

         dd($response->json());
        //  return $response->json();
    }
    
   
}
