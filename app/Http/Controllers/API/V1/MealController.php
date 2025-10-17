<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Collection;

class MealController extends Controller
{
    /**
     * @return Collection
     */
    /**
     * Получение типов питаний
     */
    public function index()
    {
        $meals = Meal::all();

        return response()->json($meals);
    }

}
