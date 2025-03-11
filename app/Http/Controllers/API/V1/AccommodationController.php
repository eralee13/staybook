<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AccommodationResource;
use App\Models\Accommodation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AccommodationController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return AccommodationResource::collection(Accommodation::all());
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id){
        try {
            $accom = Accommodation::findOrFail($id);
            return response()->json($accom, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Accommodation not found'], 404);
        }
    }
}
