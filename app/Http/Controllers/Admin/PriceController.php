<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-book|edit-book|delete-book', ['only' => ['index','show']]);
        $this->middleware('permission:create-book', ['only' => ['create','store']]);
        $this->middleware('permission:edit-book', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-book', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $hotels = Hotel::all();
        $hotel_id = $request->session()->get('hotel_id');
        $categories = Rate::where('hotel_id', $hotel_id)->get();
        $bookings = Book::where('hotel_id', $hotel_id)->where('price', '!=', null)->get();
        $rooms = Room::where('hotel_id', $hotel_id)->get();
        $events = array();

        foreach ($bookings as $booking){
            $events[] = [
                'id' => $booking->id,
                'hotel_id' => $booking->hotel_id,
                'room_id' => $booking->room_id,
                'title' => $booking->title,
                'phone' => $booking->phone,
                'email' => $booking->email,
                //'count' => $booking->count,
                //'countc' => $booking->countc,
                'sum' => $booking->sum,
                'status' => $booking->status,
                'start' => $booking->start_d,
                'end' => $booking->end_d,
            ];
        }
        return view('auth.prices.index', compact('events', 'bookings', 'rooms', 'categories', 'hotel_id', 'hotels'));
    }

    public function create()
    {
        $hotels = Hotel::all();
        $rooms = Room::all();
        return view('auth.prices.form', compact('hotels', 'rooms'));
    }

    public function store(Request $request)
    {
        $plan = Rate::where('id', $request->room_id)->first();
        $params = $request->all();

        Book::create($params);
        session()->flash('success', 'Booking created');
        return redirect()->route('prices.index');
    }


    public function edit(Book $booking)
    {
        return view('auth.prices.form', compact('booking'));
    }

    public function update(Request $request, $id)
    {
        $booking = Book::find($id);
        if(!$booking){
            return response()->json([
                'error' => 'Unable to locate the event'
            ], 404);
        }
        $booking->update([
            'start_d' => $request->start_d,
            'end_d' => $request->end_d
        ]);
        return response()->json('Event updated');
    }

    public function destroy(Book $book)
    {
        //dd($book);
//        $booking = Book::find($id);
//        if(!$booking){
//            return response()->json([
//                'error' => 'Unable to locate the event'
//            ], 404);
//        }
        $book->delete();
        session()->flash('success', 'Booking ' . $book->title . ' deleted');
        return redirect()->route('prices.index');
//        $room = Room::where('id', $request->room_id)->firstOrFail();
//        $room->increment('count', $request->count);
//        return $id;
    }

}
