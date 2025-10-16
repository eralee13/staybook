<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\BookRequest;
use App\Models\Book;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * @param $id
     * @return JsonResponse
     */
    /**
     * Получение данного бронирования
     */
    public function show(int $id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Booking not found',
                ]
            ], 404);
        }

        return response()->json($this->formatBooking($book));
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    /**
     * Создание бронирования
     */
    public function store(BookRequest $request)
    {
        try {
            $book = Book::create($request->validated());
            return response()->json([
                'message' => 'Booking created successfully',
                'data'    => $this->formatBooking($book),
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    /**
     * Отмена бронирования
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_token' => 'required|string|min:15|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code'    => 'validation_error',
                    'message' => $validator->errors(),
                ]
            ], 422);
        }

        $book = Book::where('book_token', $request->book_token)->first();

        if (!$book) {
            return response()->json([
                'error' => [
                    'code'    => 'not_found',
                    'message' => 'Booking not found',
                ]
            ], 404);
        }

        // 🔸 Проверяем текущий статус
        if (strtolower($book->status) === 'cancelled') {
            return response()->json([
                'error' => [
                    'code'    => 'already_cancelled',
                    'message' => 'This booking has already been cancelled.',
                ]
            ], 409); // 409 Conflict — логичный HTTP код
        }

        $book->status = 'Сancelled'; // можно в нижнем регистре для единообразия
        $book->save();

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data'    => $this->formatBooking($book),
        ]);
    }

    /**
     * Формат вывода данных брони (эквивалент toArray())
     */
    protected function formatBooking(Book $book): array
    {
        return [
            'id'            => $book->id,
            'hotel_id'      => $book->hotel_id,
            'room_id'       => $book->room_id,
            'rate_id'       => $book->rate_id,
            'title'         => $book->title,
            'phone'         => $book->phone,
            'email'         => $book->email,
            'adult'         => $book->adult,
            'childages'     => $book->childages,
            'arrivalDate'   => $book->arrivalDate,
            'departureDate' => $book->departureDate,
            'sum'           => $book->sum,
            'user_id'       => $book->user_id,
            'book_token'    => $book->book_token,
            'status'        => $book->status,
            'created_at'    => $book->created_at,
        ];
    }
}
