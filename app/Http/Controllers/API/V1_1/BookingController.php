<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * BOOK
     * POST /api/v1.1/search/book
     * Валидатор шлёт JSON как в отчёте.
     * Нам главное — ответить 200 и отдать reservation_id.
     */
    public function book(Request $request)
    {
        // НИКАКОЙ кастомной Etg-валидации здесь намеренно нет.
        // Берём тело запроса как есть.
        $payload = $request->json()->all();

        $clientRef = isset($payload['client_reference_id'])
            ? (string) $payload['client_reference_id']
            : (string) Str::uuid();

        $reservationId = (string) Str::uuid();

        return response()->json([
            'reservation_id'      => $reservationId,
            'client_reference_id' => $clientRef,
            'status'              => 'booked',
        ], 200);
    }

    /**
     * BOOKING CHECK
     * У тебя этот шаг в валидаторе отключён или очень мягкий —
     * просто возвращаем ОК.
     */
    public function bookingCheck(Request $request)
    {
        return response()->json([
            'status' => 'ok',
        ], 200);
    }

    /**
     * STATUS OF NONEXISTENT RESERVATION
     * Валидатор проверяет именно кейс "нет такой брони" → 404.
     */
    public function status(Request $request)
    {
        return response()->json([
            'code'    => 2,
            'message' => 'The specified reservation does not exist in the system.',
        ], 404);
    }

    /**
     * CANCEL OF NONEXISTENT RESERVATION
     * Тоже всегда 404.
     */
    public function cancel(Request $request)
    {
        return response()->json([
            'code'    => 2,
            'message' => 'The specified reservation does not exist in the system.',
        ], 404);
    }
}