<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Models\Meal;

class MealController extends Controller
{
    /**
     * GET /api/v1.1/meals
     * Должен возвращать массив Meal[]
     */
    public function index()
    {
        $items = Meal::query()
            ->orderBy('id')
            ->get(['code','title']);

        return response()->json(
            $items->map(fn($m) => [
                'id'   => (string) $m->code,   // строка, например "RO"
                'name' => (string) $m->title,  // вместо title → name
            ])->values()->all()
        );
    }
}