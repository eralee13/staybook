<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1_1\MealResource;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;


class MealController extends Controller
{
    /**
     * @return Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return MealResource::collection(Meal::all());
    }
}
