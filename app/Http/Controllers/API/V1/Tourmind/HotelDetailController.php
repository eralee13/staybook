<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\HotelDetail;

class HotelDetailController extends Controller
{
    protected $HotelDetail;

    public function __construct(HotelDetail $HotelDetail)
    {

        $this->HotelDetail = $HotelDetail;
        
    }

    public function fetchHotelDetail(Request $request)
    {

        $requestData = $request->all();
        $data = $this->HotelDetail->getHotelDetail($requestData);
        
        return response()->json($data);
        
    }
    
}

