<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\SearchOneRequest;
use App\Http\Requests\API\V1_1\SearchRequest;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function index(SearchRequest $request)
    {
        $query = Hotel::with('rooms');
        //title
        if ($request->filled('hotel_ids')) {
            $hotel_ids = (array)$request->input('hotel_ids');
            $query->whereIn('id', $hotel_ids);
        }

        //adult
//        if ($request->filled('adults')) {
//            $adult = $request->input('adults');
//            $query->whereHas('rooms', function ($quer) use ($adult) {
//                $quer->where('count', $adult);
//            });
//        }

//        //checkin
//        if ($request->filled('check_in')) {
//            $checkin = (array)$request->input('check_in');
//            $query->where('checkin', $checkin);
//        }
//
//        //checkout
//        if ($request->filled('check_out')) {
//            $checkout = (array)$request->input('check_out');
//            $query->where('checkout', $checkin);
//        }
        $hotels = $query->get();

        return response()->json($hotels);
    }

    /**
     * @param $id
     * @param SearchOneRequest $request
     * @return JsonResponse
     */
    public function show($id, SearchOneRequest $request)
    {
        $query = Hotel::with('rooms')->where('id', $id);

        //title

        //checkin
//        if ($request->filled('check_in')) {
//            $checkin = (array)$request->input('check_in');
//            $query->where('checkin', $checkin);
//        }

        //checkout
//        if ($request->filled('check_out')) {
//            $checkout = (array)$request->input('check_out');
//            $query->where('checkout', $checkout);
//        }
        $hotels = $query->get();

        return response()->json($hotels);
    }

}
