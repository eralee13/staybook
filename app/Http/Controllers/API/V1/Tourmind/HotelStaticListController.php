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

    public function fetchHotels(Request $request, \App\Services\Tourmind\HotelStaticList $svc)
    {
        $cc       = $request->input('country', 'UA');     // ?country=UA
        $pageSize = (int) $request->input('size', 200);
        $maxPages = (int) $request->input('maxPages', 0); // 0 = без лимита

        return response()->json($svc->getHotelList($cc, $pageSize, $maxPages));
    }
}