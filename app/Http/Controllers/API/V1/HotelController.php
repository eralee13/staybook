<?php

namespace App\Http\Controllers\API\V1;

use App\Filters\V1\HotelFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\HotelCollection;
use App\Http\Resources\V1\HotelResource;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * @param Request $request
     * @return HotelCollection
     */
    public function index()
    {
        try {
            $hotels = Hotel::where('status', 1)->paginate(20);
            return response()->json($hotels);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Hotels not found'], 404);
        }
    }

    public function show($id){
        try {
            $hotel = Hotel::with('rooms', 'rates', 'cancellation')->where('status', 1)->findOrFail($id);
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
