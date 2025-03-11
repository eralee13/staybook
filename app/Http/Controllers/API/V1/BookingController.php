<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\CancelRequest;
use App\Http\Requests\API\V1\StoreBookRequest;
use App\Http\Requests\API\V1\UpdateBookRequest;
use App\Http\Resources\V1\BookingResource;
use App\Models\Book;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * @return Collection
     */
    public function index()
    {
        return BookingResource::collection(Book::all());
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $book = Book::where('status', '!=', 'Отменен пользователем')->findOrFail($id);
            return response()->json($book);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(StoreBookRequest $request)
    {
        $params = $request->validated();
        $booking = Book::create($params);
        $room = Room::where('id', $request->room_id)->firstOrFail();
        //dd($room);
        $room->decrement('count', $request->adult);

        return response()->json($booking);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateBookRequest $request, $id)
    {
        $booking = Book::findOrFail($id);
        if (!$booking) {
            return response()->json([
                'error' => 'Unable to booking'
            ], 404);
        }
        $booking->update($request->all());
        return response()->json('Book updated');
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function cancel(CancelRequest $request)
    {
        $book_token = $request->book_token;
        //$id = $request->book_id;
        try {
            $book = Book::where('book_token', $book_token)->where('status', 'Reserved')->first();
            $book->update(['status' => 'Cancelled']);
            return response()->json('Book cancelled');
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }

    }
}
