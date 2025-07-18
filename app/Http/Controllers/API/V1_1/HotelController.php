<?php

namespace App\Http\Controllers\API\V1_1;

use App\Filters\V1\HotelFilter;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1_1\HotelCollection;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * @param Request $request
     * @return HotelCollection
     */
    public function HotelStatic(Request $request)
    {
        $filter = new HotelFilter();

        if ($request->header('Content-Type') === 'application/x-ndjson') {
            $lines = explode("\n", $request->getContent());
            $parsed = [];

            foreach ($lines as $line) {
                if (trim($line)) {
                    $data = json_decode($line, true);
                    if (is_array($data)) {
                        $parsed[] = $data;
                    }
                }
            }

            $queryItems = $filter->fromArray($parsed);

            return new HotelCollection(
                count($queryItems)
                    ? Hotel::where($queryItems)->paginate(20)
                    : Hotel::paginate(20)
            );
        }

        // Стандартный запрос
        $queryItems = $filter->transform($request);
        return new HotelCollection(
            count($queryItems)
                ? Hotel::where($queryItems)->paginate(20)
                : Hotel::paginate(20)
        );
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
