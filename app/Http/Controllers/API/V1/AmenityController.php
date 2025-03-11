<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AmenityResource;
use App\Models\Amenity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AmenityController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return AmenityResource::collection(Amenity::all());
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id){
        try {
            $amenity = Amenity::findOrFail($id);
            return response()->json($amenity, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Amenity not found'], 404);
        }
    }
}
