<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $bookings = Book::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('hotel_id', $hotel)
            ->where('status', 'Reserved')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = $bookings->pluck('date');
        $counts = $bookings->pluck('count');

        $roomCount = Room::where('hotel_id', $hotel)->count();
        $rateCount = Rate::where('hotel_id', $hotel)->count();

        return view('auth.dashboard.index', compact('dates', 'counts', 'roomCount', 'rateCount', 'roomCount'));
    }
}
