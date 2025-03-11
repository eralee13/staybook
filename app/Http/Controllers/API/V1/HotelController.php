<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\HotelFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\HotelCollection;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * @param Request $request
     * @return HotelCollection
     */
    public function index(Request $request)
    {
        $filter = new HotelFilter();
        $queryItems = $filter->transform($request);//'column', 'operator', 'value'
        if(count($queryItems) == 0){
            return new HotelCollection(Hotel::paginate(20));
        } else {
            return new HotelCollection(Hotel::where($queryItems)->paginate(20));
        }
    }

    public function show($id){
        try {
            $hotel = Hotel::findOrFail($id);
            return response()->json($hotel);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Hotel not found'], 404);
        }
    }

//    /**
//     * @param StoreHotelRequest $request
//     * @return HotelResource
//     */
//    public function store(StoreHotelRequest $request)
//    {
//        return new HotelResource(Hotel::create($request->all()));
//    }
//
//    /**
//     * @param Request $request
//     * @param Hotel $hotel
//     * @return void
//     */
//    public function update(Request $request, Hotel $hotel)
//    {
//        $hotel->update($request->all());
//    }

//    /**
//     * @param Hotel $hotel
//     * @return Application|ResponseFactory|\Illuminate\Foundation\Application|Response
//     */
//    public function destroy(Hotel $hotel)
//    {
//        $hotel->delete();
//        return response(null, 204);
//    }
}
