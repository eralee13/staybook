<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1_1\MealResource;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;


class MealController extends Controller
{
    /**
     * @group Питание
     * Получить список типов питания
     *
     * @response 200 App\Http\Resources\V1_1\MealResource[]
     */
    public function meals()
    {
        return MealResource::collection(Meal::all());
    }
}
