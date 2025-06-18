<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Book;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;

class BookingCalendarPriceController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $hotelId = $request->get('hotel_id') ?? 14;

        $hotelslist = Hotel::select('id', 'title')
            ->orderBy('title', 'asc')
            ->get();

        $startDate = Carbon::now()->startOfMonth()->startOfDay();
        $endDate = Carbon::now()->endOfMonth()->endOfDay();

        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        $resources = [];
        foreach ($rooms as $room) {
            $validRates = $room->rates->filter();
            if ($validRates->isEmpty()) {
                continue;
            }

            $parentId = 'room_' . $room->id;

            $resources[] = [
                'id' => $parentId,
                'title' => $room->title,
            ];

            foreach ($validRates as $rate) {
                $resources[] = [
                    'id' => $parentId . '_rate_' . $rate->id,
                    'title' => $rate->title,
                    'parentId' => $parentId,
                ];
            }
        }

        return view('auth.books.calendarprice.index', [
            'resources' => $resources,
            'hotelslist' => $hotelslist,
            'events' => []
        ]);
    }

    public function getEvents(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $hotelId = $request->get('hotel_id') ?? 14;

        $startDate = $request->input('start')
            ? Carbon::parse($request->input('start'))->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->input('end')
            ? Carbon::parse($request->input('end'))->endOfDay()
            : now()->endOfMonth();

        $books = Book::with(['room', 'rate'])
            ->whereHas('room', fn($q) => $q->where('hotel_id', $hotelId))
            ->whereBetween('arrivalDate', [$startDate, $endDate])
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->get();

        $rooms = Room::with('rates')
            ->where('hotel_id', $hotelId)
            ->get();

        $bookingsMap = [];
        foreach ($books as $book) {
            $room = $book->room;
            $rate = $book->rate;
            if (!$room || !$rate) continue;

            $resourceId = 'room_' . $room->id . '_rate_' . $rate->id;

            $period = Carbon::parse($book->arrivalDate)->daysUntil(Carbon::parse($book->departureDate));

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');

                $bookingsMap[$resourceId][$dateStr] = [
                    'id' => $book->id,
                    'status' => $book->status,
                    'price' => $book->price,
                    'currency' => $book->currency,
                    'phone' => $book->phone,
                    'email' => $book->email,
                    'adult' => $book->adult,
                ];
            }
        }

        $resources = [];
        $events = [];

        foreach ($rooms as $room) {
            $parentId = 'room_' . $room->id;
            $resources[] = [
                'id' => $parentId,
                'title' => $room->title,
            ];

            foreach ($room->rates as $rate) {
                $resourceId = $parentId . '_rate_' . $rate->id;
                $resources[] = [
                    'id' => $resourceId,
                    'title' => $rate->title,
                    'parentId' => $parentId,
                ];

                $period = $startDate->daysUntil($endDate);
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    $isBooked = $bookingsMap[$resourceId][$dateStr] ?? null;
                    $price = $isBooked ? $isBooked['price'] : $rate->price;
                    $currency = $isBooked ? $isBooked['currency'] : ($rate->currency ?? '$');

                    $events[] = [
                        'id' => ($isBooked ? $isBooked['id'] : 'free_' . $rate->id) . '_' . $dateStr,
                        'title' => $price . ' ' . $currency,
                        'start' => $dateStr,
                        'end' => $dateStr,
                        'resourceId' => $resourceId,
                        'backgroundColor' => $isBooked ? '#d95d5d' : '#39bb43',
                        'borderColor' => $isBooked ? '#d95d5d' : '#39bb43',
                        'extendedProps' => [
                            'room_id' => $room->id,
                            'rate_id' => $rate->id,
                            'description' => $isBooked
                                ? ($isBooked['price'] . ' ' . $isBooked['currency'] . '<br>' . $isBooked['phone'] . '<br>' . $isBooked['email'])
                                : '',
                        ]
                    ];
                }
            }
        }

        return response()->json([
            'events' => $events,
            'resources' => $resources,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'start' => 'required|date',
                'end' => 'required|date|after_or_equal:start',
                'rate_id' => 'required|exists:rates,id',
                'room_id' => 'required|exists:rooms,id',
                'hotel_id' => 'required|exists:hotels,id',
                'allotment' => 'required|numeric|min:0',
            ]);

            $start = Carbon::parse($validated['start'])->format('Y-m-d');
            $end = Carbon::parse($validated['end'])->format('Y-m-d');
            $rateId = $validated['rate_id'];
            $roomId = $validated['room_id'];
            $hotelId = $validated['hotel_id'];
            $allotment = $validated['allotment'];

            $rate = Rate::find($rateId);
            if ((int) $rate->room_id !== (int) $roomId) {
                return response()->json([
                    'error' => true,
                    'message' => 'Несоответствие тарифа и номера.'
                ]);
            }

            do {
                $token = Str::random(40);
            } while (Book::where('book_token', $token)->exists());

            $book = Book::create([
                'book_token' => $token,
                'title' => '',
                'title2' => '',
                'hotel_id' => $hotelId,
                'room_id' => $roomId,
                'rate_id' => $rateId,
                'phone' => '',
                'email' => '',
                'comment' => '',
                'adult' => 0,
                'child' => null,
                'price' => $allotment,
                'sum' => 0,
                'currency' => $rate->currency ?? '$',
                'arrivalDate' => $start,
                'departureDate' => $end,
                'status' => 'Pending',
                'user_id' => Auth::id(),
                'api_type' => 'calendar',
            ]);

            return response()->json(['success' => true, 'message' => 'Бронь успешно создана.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => implode('<br>', $e->validator->errors()->all())
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => 'Ошибка сервера: ' . $th->getMessage()
            ]);
        }
    }
}
