<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\HotelStaticList;

class HotelStaticListController extends Controller
{
    protected $HotelStaticList;

    public function __construct(HotelStaticList $HotelStaticList)
    {

        $this->HotelStaticList = $HotelStaticList;
        
    }

    public function fetchHotels(Request $request)
    {

        $requestData = $request->all();
        // $data = $this->HotelStaticList->getHotelListForAllCountries($requestData);
        $data = $this->HotelStaticList->getHotelList($requestData);
        
        return response()->json($data);
        
    }
    
}

