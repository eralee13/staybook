<?php

namespace App\Http\Controllers\API\V1\Tourmind;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Tourmind\HotelDetail;

class HotelDetailController extends Controller
{
    private HotelDetail $hotelDetail;

    public function __construct(HotelDetail $hotelDetail)
    {
        $this->hotelDetail = $hotelDetail;
    }

    public function fetchHotelDetail(Request $request)
    {
        $data = $this->hotelDetail->getHotelDetail($request->all());
        return response()->json($data);
    }
}