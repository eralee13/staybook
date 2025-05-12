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

}
