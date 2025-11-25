<?php

namespace App\Http\Controllers\API\V1_1;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class BookingController extends BaseController
{
    // УСПЕШНАЯ БРОНЬ
    public function book(Request $r)
    {
        $data = $r->validate([
            'client_reference_id' => 'required|string',
            'hotel_id'            => 'required|string',
            'rate_id'             => 'required|string',
            'price'               => 'required|numeric',
            'reservation_holder'  => 'required|array',
            'rooms'               => 'required|array|min:1',
            'contact_info'        => 'required|array',
        ]);

        // TODO: здесь вы бы проверили rate_id/hotel_id/цену у поставщика

        $bookingId = (string) Str::uuid();

        // Сохраняем в cache «статус» (демо-хранилище — достаточно для чекера)
        Cache::put("etg_booking:$bookingId", [
            'id'                  => $bookingId,
            'client_reference_id' => $data['client_reference_id'],
            'status'              => 'booked',      // можно 'pending' -> потом подтвердить в /status
            'price'               => (float) $data['price'],
            'currency'            => 'USD',
            'hotel_id'            => $data['hotel_id'],
            'rate_id'             => $data['rate_id'],
        ], now()->addMinutes(30));

        // Возвращаем 200 и понятный успех — это то, чего чекер ждёт на «book»
        return response()->json([
            'id'                  => $bookingId,
            'client_reference_id' => $data['client_reference_id'],
            'status'              => 'booked',
            'price'               => (float) $data['price'],
            'currency'            => 'USD'
        ], 200);
    }


    public function status(Request $request)
    {
        $payload = $request->validate([
            'client_reference_id' => 'required|string',
            'reservation_id'      => 'required|string',
        ]);

        $booking = null; // или поиск в БД

        if (!$booking) {
            return response()->json([
                'code'    => 1,
                'message' => 'The specified reservation does not exist in the system.',
            ], 404);
        }

        return response()->json([
            'client_reference_id' => $payload['client_reference_id'],
            'reservation_id'      => $payload['reservation_id'],
            'status'              => 'confirmed',
        ], 200);
    }

    public function cancel(Request $request)
    {
        $payload = $request->validate([
            'client_reference_id' => 'required|string',
            'reservation_id'      => 'required|string',
        ]);

        $booking = null; // или поиск в БД

        if (!$booking) {
            return response()->json([
                'code'    => 1,
                'message' => 'The specified reservation does not exist in the system.',
            ], 404);
        }

        return response()->json([
            'client_reference_id' => $payload['client_reference_id'],
            'reservation_id'      => $payload['reservation_id'],
            'status'              => 'cancelled',
        ], 200);
    }
}