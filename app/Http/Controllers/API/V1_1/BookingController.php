<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\CancelRequest;
use App\Http\Requests\API\V1_1\BookRequest;
use App\Http\Requests\API\V1_1\BookStatusRequest;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * @param BookRequest $request
     * @return JsonResponse
     */
    public function store(BookRequest $request)
    {
        try {
            Book::create($request->validated());
            return response()->json('Book created successfully', 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e]);
        }
    }

    /**
     * @param BookStatusRequest $request
     * @return JsonResponse
     */
    public function getStatus(BookStatusRequest $request)
    {
        try {
            $booking = Book::where('id', $request->id)->where('book_token', $request->book_token)->firstOrFail();
            return response()->json(
                [
                    'status' => $booking->status,
                    'reservation_id' => $booking->id,
                    'client_reference_id' => $booking->book_token,
                    'firstName' => $booking->title,
                    'phone' => $booking->phone,
                    'email' => $booking->email,
                    'arrivalDate' => $booking->arrivalDate,
                    'departureDate' => $booking->departureDate,
                ]
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e]);
        }
    }


    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function cancelBook(CancelRequest $request)
    {
        $book_token = $request->book_token;
        try {
            $book = Book::where('book_token', $book_token)->where('status', 'Reserved')->first();
            $book->update(['status' => 'Cancelled']);
            return response()->json('Book cancelled');
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }

    }
}
