<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\CancelRequest;
use App\Http\Requests\API\V1_1\BookRequest;
use App\Http\Requests\API\V1_1\BookStatusRequest;
use App\Models\Book;
use Carbon\Carbon;
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
    public function book(BookRequest $request)
    {
        try {
            $validated = $request->validated();
            $data = [
                'hotel_id' => $validated['hotel_id'],
                'rate_id' => $validated['rate_id'],
                'book_token' => $validated['client_reference_id'],
                'title' => $validated['firstname'] . ' ' . $validated['lastname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'sum' => $validated['price'],
                'room_id' => $validated['room_id'],
                'comment' => $validated['comment'],
                'arrivalDate' => $validated['arrivalDate'],
                'departureDate' => $validated['departureDate'],
                'adult' => $validated['adult'],
                'childages' => $validated['childages'],
                'cancellation_id' => $validated['cancellation_rule_id'],
                'user_id' => 1,
                'currency' => '$',
                'status' => 'Reserved'
            ];

            $book = Book::create($data);

            //return new BookResource($book);

            return response()->json('Book created successfully', 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e]);
        }
    }

    /**
     * @param BookStatusRequest $request
     * @return JsonResponse
     */
    public function status(BookStatusRequest $request)
    {
        try {
            $booking = Book::where('id', $request->reservation_id)->where('book_token', $request->client_reference_id)->firstOrFail();
            $cancelDate = Carbon::parse($booking->arrivalDate)->subDays($booking->rate->cancellationRule->free_cancellation_days);
            return response()->json(
                [
                    'reservation_id' => $booking->id,
                    'client_reference_id' => $booking->book_token,
                    'hotel_confirmation_code' => $booking->hotel_confirmation_code ?? '123',
                    'status' => $booking->status,
                    'check_in' => $booking->arrivalDate,
                    'check_out' => $booking->departureDate,
                    'reservation_holder' => [
                        'first_name' => $booking->title,
                        'last_name' => $booking->title,
                        'is_child' => false
                    ],
                    'contact_info' => [
                        'phone' => $booking->phone,
                        'email' => $booking->email,
                    ],
                    'hotel_id' => $booking->hotel_id,
                    'hotel_name' => $booking->hotel->title,
                    'rate' => [
                        'id' => $booking->rate_id,
                        'price' => $booking->rate->price,
                        'bar_price' => null,
                        'comission' => null,
                        'supplier_min_price' => null,
                        'taxes' => [
                            'type' => '',
                            'currency' => $booking->rate->currency ?? 'USD',
                            'is_included' => false,
                            'amount' => 0
                        ],
                        'payment_type' => 'prepayment',
                        'currency' => $booking->rate->currency ?? 'USD',
                        'meals' => [
                            'id' => $booking->rate->meal->code,
                            'name' => $booking->rate->meal->title,
                        ],
                        'cancellation_policies' => [
                            'from' => $cancelDate,
                            'amount' => $booking->cancel_penalty,
                        ],
                    ],
                    'rooms' => [
                        'id' => $booking->room_id,
                        'name' => $booking->room->title,
                        'bed_groups' => [
                            'id' => $booking->rate->id,
                            'name' => $booking->rate->title,
                        ],
                        'allotment' => $booking->rate->availability,
                    ],
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
    public function cancel(CancelRequest $request)
    {
        $book_token = $request->book_token;
        try {
            $book = Book::where('book_token', $book_token)->where('status', 'Reserved')->first();
            $book->update(['status' => 'Cancelled']);
            return response()->json(
                [
                    'reservation_id' => $book->id,
                    'client_reference_id' => $book->book_token,
                    'hotel_confirmation_code' => 123,
                    'status' => $book->status,
                ]
            );
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }

    }
}
