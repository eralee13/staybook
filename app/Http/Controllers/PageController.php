<?php

namespace App\Http\Controllers;

use App\Http\Requests\MultiForm\HotelOneRequest;
use App\Http\Requests\MultiForm\HotelTwoRequest;
use App\Models\Book;
use App\Models\City;
use App\Models\Rate;
use App\Models\Contact;
use App\Models\Meal;
use App\Models\Image;
use App\Models\Page;
use App\Models\Room;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


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

        // Sorting by hotel rating
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

    public function hotels()
    {
        $hotels = Hotel::cacheFor(now()->addHours(24))->orderBy('top', 'DESC')->paginate(30);
        return view('pages.hotels', compact('hotels'));
    }


    public function allrooms()
    {
        $rooms = Room::cacheFor(now()->addHours(24))->where('status', 1)->orderBy('price', 'asc')->paginate(30);
        return view('pages.rooms', compact('rooms'));
    }

    public function room($hotel, $roomCode)
    {
        $room = Room::cacheFor(now()->addHours(24))->withTrashed()->byCode($roomCode)->firstOrFail();
        $user = Auth::id();
        $random = str()->random(15);
        $images = Image::where('room_id', $room->id)->get();
        //$related = Room::where('id', '!=', $room->id)->where('hotel_id', $room->hotel_id)->where('status', 1)->orderBy('price', 'asc')->get();
        $related = Room::where('id', '!=', $room->id)->where('hotel_id')->where('status', 1)->orderBy('created_at', 'DESC')->get();
        return view('pages.room', compact('room', 'images', 'related', 'random', 'user'));
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


    public function createStepOne(Request $request)
    {
        $hotel = $request->session()->get('hotelInfo');

        return view('create-step-one', compact('hotel'));
    }


    //multiform

    public function postCreateStepOne(HotelOneRequest $request)
    {

        if (empty($request->session()->get('hotelInfo'))) {
            $hotel = new Hotel();
            $hotel->fill($request->all());
            $request->session()->put('hotelInfo', $hotel);
        } else {
            $hotel = $request->session()->get('hotelInfo');
            $hotel->fill($request->all());
            $request->session()->put('hotelInfo', $hotel);
        }

        return redirect()->route('createStepTwo');
    }


    public function createStepTwo(Request $request)
    {
        $request['code'] = Str::slug($request->title);
        $hotel = $request->session()->get('hotelInfo');
        dd($request->title);

        return view('create-step-two', compact('hotel'));
    }


    public function postCreateStepTwo(HotelTwoRequest $request)
    {
//        $validatedData = $request->validate([
//            'count' => 'required',
//            'description' => 'required',
//        ]);

        $hotel = $request->session()->get('hotelInfo');
        $hotel->fill($request->all());
        $request->session()->put('hotelInfo', $hotel);

        return redirect()->route('createStepThree');
    }

    public function createStepThree(Request $request)
    {
        $hotel = $request->session()->get('hotelInfo');
        //dd($hotel);

        return view('create-step-three', compact('hotel'));
    }

    public function postCreateStepThree(Request $request)
    {
        $hotel = $request->session()->get('hotelInfo');
        $hotel->save();
        $request->session()->forget('hotelInfo');

        return redirect()->route('index');
    }

    public function searchtest(Request $request)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties?count=20&include=All');
        $properties = $response->object()->properties;

        $query = Hotel::with('rates', 'meals', 'rooms');
        $start = Carbon::createFromDate($request->start_d);
        $end = Carbon::createFromDate($request->end_d);
        $count_day = $start->diffInDays($end);
        $count = $request->count;

        //title
        if ($request->filled('title')) {
            $title = $request->input('title');
            $query->where('id', $title);
            //$query->orWhere('address', '%like%', $title);
            $properties = collect($properties)->where('id', $title)->all();
        }

        //count
        if ($request->filled('count')) {
            $count = $request->input('count');
            $query->whereHas('rooms', function ($quer) use ($count) {
                $quer->where('price2', '!=', null);
            });
        }

        //rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        //food
//        if ($request->filled('food_id')) {
//            $food = $request->input('food_id');
//            $query->whereHas('food', function ($quer) use ($food) {
//                $quer->where('title_en', $food);
//            });
//        }

        //early
        if ($request->filled('early_in')) {
            $early_in = $request->input('early_in');
            $query->where('early_in', $early_in);
        }

        //late
        if ($request->filled('early_out')) {
            $early_out = $request->input('early_out');
            $query->where('early_out', $early_out);
        }

        //cancellations
//        if ($request->filled('cancelled')) {
//            $cancel = $request->input('cancelled');
//            $query->whereHas('rule', function ($quer) use ($cancel) {
//                $quer->where('size', 0);
//            });
//        }

        //extra
//        if ($request->filled('extra_place')) {
//            $extra_place = $request->input('extra_place');
//            $query->whereHas('child', function ($quer) use ($extra_place) {
//                $quer->where('price_extra', '!=', 0);
//                $quer->orWhere('price_extra', '!=', null);
//            });
//        }

        $hotels = $query->orderBy('top', 'desc')->get();
        $contacts = Contact::get();

//        if ($request->filled('daterange')) {
//            $query->whereBetween('price',[$request->left_value, $request->right_value]);
//        }

        $relhotels = Hotel::where('status', 1)->inRandomOrder()->get();
        $relprops = $response->object()->properties;

        return view('pages.searchtest', compact('hotels', 'contacts', 'relhotels', 'count', 'count_day', 'start', 'end', 'query', 'request', 'properties', 'relprops'));
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

//    public function getBooks()
//    {
//        $user_id = Auth::id();
//        $books = Book::where('user_id', $user_id)->where('book_token', '!=', '')->where('status', 'Оплачено')->get();
//        return view('pages.cancel-order', compact('books'));
//    }
//
//    public function cancelBook(Request $request)
//    {
//        $id = $request->book_id;
//        $token_book = $request->book_token;
//        if($token_book != null){
//            Book::where('id', $id)->update(['status' => 'Отменен пользователем']);
//            session()->flash('success', 'Booking ' . $request->title . ' is cancelled');
//            return redirect()->route('index');
//        }
//        else {
//            session()->flash('danger', 'Error');
//            return redirect()->back();
//        }
//    }

    public function testsearch()
    {
        //$books = Book::whereBetween('arrivalDate', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->get();
        $bookingInfo = Book::with('rooms')->where('user_id', auth()->id())->get([
            'id',
            'room_id',
            'arrivalDate',
            'departureDate',
            //'days',
            'sum',
            'status',
        ]);
        return response()->json($bookingInfo);
        //return view('pages.testsearch', compact('books'));
    }

    // Метод, отдающий массив забронированных дат на просматриваемом номере отеля
    public function getBookedDates(Request $request, $room_id): \Illuminate\Http\JsonResponse
    {

        $bookedDates = Book::where('room_id', $room_id)->get(['arrivalDate', 'departureDate']);

        foreach ($bookedDates as $book) {
            $startDate = Carbon::parse($book->arrivalDate);
            $endDate = Carbon::parse($book->departureDate);
            // Количетсво дней (включительно (+1))
            $numberOfDays = $startDate->diffInDays($endDate) + 1;
        }

        return response()->json($bookedDates);
    }


}
