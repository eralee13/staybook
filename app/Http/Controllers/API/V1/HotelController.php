<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Получение всех отелей
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

    /**
     * Получение определенного отеля
     */
    public function show($id){
        try {
            $hotel = Hotel::with('rooms', 'rates', 'cancellation')->where('status', 1)->findOrFail($id);
            return response()->json($hotel);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Hotel not found'], 404);
        }
    }

}
