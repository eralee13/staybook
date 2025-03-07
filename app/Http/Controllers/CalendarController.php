<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Room;
use Illuminate\Http\Request;

class CalendarController extends Controller
{

    public function index($id)
    {
        $room = Room::where('id', $id)->firstOrFail();
        $events = array();
        $bookings = Book::where('room_id', $id)->get();
        foreach ($bookings as $booking){
            $color = null;
            if($booking->title == 'ariet'){
                $color = 'red';
            }
            $events[] = [
                'id' => $booking->id,
                'room_id' => $booking->room_id,
                'title' => $booking->title,
                'phone' => $booking->phone,
                'email' => $booking->email,
                'comment' => $booking->comment,
                'count' => $booking->count,
                'start' => $booking->start_d,
                'end' => $booking->end_d,
                'color' => $color
            ];
        }
        return view('pages.books', compact('id', 'events', 'room'));
    }

    public function store(Request $request)
    {
        $request->validate([
           'title' => 'required|string'
        ]);
        $params = $request->all();
        $booking = Book::create($params);
        $room = Room::where('id', $request->room_id)->firstOrFail();
        $room->decrement('count', $request->count);
        session()->flash('success', 'Бронирование ' . $booking->title . ' создано');
        //return response()->json($booking);
        return redirect()->route('books.index', $room->id);
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

    public function delete(Request $request, $id)
    {
        $booking = Book::find($id);
        if(!$booking){
            return response()->json([
                'error' => 'Unable to locate the event'
            ], 404);
        }
        $booking->delete();
        return $id;
    }
}
