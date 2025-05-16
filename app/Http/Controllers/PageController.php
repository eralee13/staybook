<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Room;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::where('tourmind_id', null)->get();
        $cities = City::where('country_id', null)->orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        return view('index', compact('hotels', 'cities', 'tomorrow'));
    }

    public function search(Request $request)
    {
        $cities = City::where('country_id', null)->orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $query = Hotel::with(['rates' => function ($q) use ($request) {
//            if ($request->filled('min_price')) {
//                $q->where('price', '>=', $request->min_price);
//            }
//
//            if ($request->filled('max_price')) {
//                $q->where('price', '<=', $request->max_price);
//            }

            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }

            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }

            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

//            if ($request->boolean('early_in')) {
//                $q->where('early_in', true);
//            }
//
//            if ($request->boolean('late_out')) {
//                $q->where('late_out', true);
//            }

            // Показать только те тарифы, у которых нет бронирования
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

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('rating')) {
            $query->where('rating', '>=', $request->rating);
        }

        if ($request->sort === 'highest_rating') {
            $query->orderBy('rating', 'desc');
        } elseif ($request->sort === 'lowest_rating') {
            $query->orderBy('rating', 'asc');
        }

        $hotels = $query->get()->filter(function ($hotel) {
            return $hotel->rates->isNotEmpty();
        });

        if ($request->sort === 'lowest_price') {
            $hotels = $hotels->sortBy(fn($h) => $h->rates->min('price'));
        } elseif ($request->sort === 'highest_price') {
            $hotels = $hotels->sortByDesc(fn($h) => $h->rates->max('price'));
        }
        $related = Hotel::where('tourmind_id', null)->whereIn('id', [14, 15])->get();

        return view('pages.search', compact('hotels', 'cities', 'tomorrow', 'request', 'related'));
    }

    public function hotel($code, Request $request)
    {
        $hotel = Hotel::cacheFor(now()->addHours(24))->where('code', $code)->first();
        $arrival = Carbon::createFromDate($request->arrivalDate);
        $departure = Carbon::createFromDate($request->departureDate);
        $count_day = $arrival->diffInDays($departure);
        $adult = $request->adult;

        $query = Room::with(['rates' => function ($q) use ($request) {
            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }

            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }

            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

            // Показать только те тарифы, у которых нет бронирования
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
        }])->where('hotel_id', $hotel->id);

        $rooms = $query->get()->filter(function ($room) {
            return $room->rates->isNotEmpty();
        });

        if ($hotel->exely_id != null) {
            return view('pages.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
        } else {
            return view('pages.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
        }
    }

    public function order(Request $request)
    {
        //dd($request->all());
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.order', compact('request', 'arrival', 'departure'));
    }

    public function book_verify(Request $request)
    {
        $arrival = Carbon::createFromDate($request->arrivalDate)->format('d.m.Y');
        $departure = Carbon::createFromDate($request->departureDate)->format('d.m.Y');

        return view('pages.order-verify', compact('request', 'arrival', 'departure'));
    }

    public function book_reserve(Request $request)
    {
        $date = date('Ymd'); // текущая дата: 20250507
        $part1 = random_int(100000, 999999); // 6-значное число
        $part2 = random_int(1000000000, 9999999999); // 10-значное число
        $str = "{$date}-{$part1}-{$part2}";
        $data = [
            'hotel_id' => $request->get('propertyId'),
            'room_id' => $request->get('roomTypeId'),
            'rate_id' => $request->get('ratePlanId'),
            'cancellation_id' => $request->get('cancellation_id'),
            'cancel_penalty' => $request->get('cancelPrice'),
            'arrivalDate' => $request->get('arrivalDate'),
            'departureDate' => $request->get('departureDate'),
            'currency' => $res->booking->currencyCode ?? '$',
            'title' => $request->get('firstName'),
            'phone' => $request->get('phone'),
            'email' => $request->get('email'),
            'comment' => $request->get('comment'),
            'adult' => $request->get('adultCount'),
            'child' => $request->get('child'),
            'childAges' => implode(',', $request->get('childAges')),
            'sum' => $request->get('total'),
            'status' => 'Reserved',
            'book_token' => $res->booking->number ?? $str,
            'user_id' => Auth::id() ?? '1',
        ];
        $book = Book::create($data);

        return view('pages.order-reserve', compact('book'));
    }

    public function cancel_calculate(Request $request)
    {
        $book = Book::where('book_token', $request->number)->firstOrFail();
        return view('pages.cancel-calculate', compact('book', 'request'));
    }

    public function cancel_confirm(Request $request, Book $book)
    {
        $book->where('book_token', $request->number)->update([
            'status' => "Cancelled"
        ]);
        $book = Book::where('book_token', $request->number)->firstOrFail();
        return view('pages.cancel-confirm', compact('book', 'request'));
    }

    public function about(Request $request)
    {
        $page = Page::cacheFor(now()->addHours(24))->where('id', 4)->first();
        return view('pages.about', compact('page', 'request'));
    }

    public function contactspage()
    {
        $page = Page::cacheFor(now()->addHours(24))->where('id', 5)->first();;
        $contacts = Contact::get();
        return view('pages.contacts', compact('page', 'contacts'));
    }

    public function book_mail(Request $request)
    {
        $params = $request->all();
        Book::create($params);
        //Mail::to('info@silkwaytravel.kg')->cc($request->email)->bcc($hotel->email)->send(new BookMail($request));
        //Mail::to('info@timdjol.com')->cc($request->email)->send(new BookMail($request));
        session()->flash('success', 'Booking ' . $request->title . ' is created');
        return redirect()->route('index');
    }

}
