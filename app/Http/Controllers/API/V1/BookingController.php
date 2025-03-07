<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;


class BookingController extends Controller
{
    public function index()
    {
        return Book::all();
    }
}