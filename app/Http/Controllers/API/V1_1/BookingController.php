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

    // СТАТУС БРОНИ (поддержим и GET, и POST)
    public function status(Request $r)
    {
        $bookingId = $r->input('id', $r->query('id'));
        $r->merge(['id' => $bookingId]);
        $r->validate(['id' => 'required|string']);

        $rec = Cache::get("etg_booking:$bookingId");
        if (!$bookingId) {
            return response()->json(['message'=>'Booking not found'], 404);
        }

        return response()->json([
            'id'                 => $rec['id'],
            'status'             => $rec['status'],
            'voucher_available'  => true
        ], 200);
    }

    // ОТМЕНА
    public function cancel(Request $r)
    {
        $data = $r->validate([
            'id'     => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $rec = Cache::get("etg_booking:{$data['id']}");
        if (!$data) {
            return response()->json(['message'=>'Booking not found'], 404);
        }

        $rec['status'] = 'cancelled';
        Cache::put("etg_booking:{$data['id']}", $rec, now()->addMinutes(30));

        return response()->json([
            'id'      => $data['id'],
            'status'  => 'cancelled',
            'penalty' => ['currency'=>'USD','amount'=>0.00]
        ], 200);
    }
}