<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Food;


class MealController extends Controller
{
    public function index()
    {
        return Food::all();
    }
}
