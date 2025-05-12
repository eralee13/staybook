<?php

namespace App\Http\Controllers\API\V1_1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1_1\SearchOneRequest;
use App\Http\Requests\API\V1_1\SearchRequest;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function index(SearchRequest $request)
    {
        $query = Hotel::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }

            if ($request->filled('start_d') && $request->filled('end_d')) {
                $startTime = $request->start_d;
                $endTime = $request->end_d;

                $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                    $b->where('status', 'reserved')
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->whereBetween('arrivalDate', [$startTime, $endTime])
                                ->orWhereBetween('departureDate', [$startTime, $endTime])
                                ->orWhere(function ($q) use ($startTime, $endTime) {
                                    $q->where('arrivalDate', '<=', $startTime)
                                        ->where('departureDate', '>=', $endTime);
                                });
                        });
                });
            }

        }]);
        //city
        if ($request->filled('city')) {
            $query->where('city', $request->get('city'));
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        $hotels = $query->where('status', 1)->get();

        return response()->json($hotels);
    }

    /**
     * @param $id
     * @param SearchOneRequest $request
     * @return JsonResponse
     */
    public function show($id, SearchOneRequest $request)
    {
        $query = Room::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }

            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }
            if ($request->filled('arrivalDate') && $request->filled('departureDate')) {
                $startTime = $request->arrivalDate;
                $endTime = $request->departureDate;

                $q->whereDoesntHave('bookings', function ($b) use ($startTime, $endTime) {
                    $b->where('status', 'reserved')
                        ->where(function ($query) use ($startTime, $endTime) {
                            $query->whereBetween('arrivalDate', [$startTime, $endTime])
                                ->orWhereBetween('departureDate', [$startTime, $endTime])
                                ->orWhere(function ($q) use ($startTime, $endTime) {
                                    $q->where('arrivalDate', '<=', $startTime)
                                        ->where('departureDate', '>=', $endTime);
                                });
                        });
                });
            }
        }])->where('hotel_id', $id);

        $rooms = $query->get()->filter(function ($room) {
            return $room->rates->isNotEmpty();
        });

        return response()->json($rooms);
    }

}
