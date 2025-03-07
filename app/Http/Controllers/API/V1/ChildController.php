<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Room;


class ChildController extends Controller
{
    public function index()
    {
        return Child::all();
    }
}