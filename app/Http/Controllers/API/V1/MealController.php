<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MealResource;
use App\Models\Meal;
use App\Models\Rule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;


class MealController extends Controller
{
    /**
     * @return Collection
     */
    public function index()
    {
        return MealResource::collection(Meal::all());
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id){
        try {
            $meal = Meal::findOrFail($id);
            return response()->json($meal, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Meal not found'], 404);
        }
    }
}
