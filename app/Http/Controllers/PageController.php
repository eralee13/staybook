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
        $hotels = Hotel::all();
        $rooms = Room::cacheFor(now()->addHours(24))->where('status', 1)->inRandomOrder()->paginate(40);
        $foods = Meal::all();
        return view('index', compact('hotels', 'rooms', 'foods'));
    }

    public function hotels()
    {
        $hotels = Hotel::cacheFor(now()->addHours(24))->orderBy('top', 'DESC')->paginate(30);
        return view('pages.hotels', compact('hotels'));
    }

    public function hotel($code, Request $request)
    {
        $hotel = Hotel::cacheFor(now()->addHours(24))->where('code', $code)->first();
        //dd($hotel->exely_id);
        $start = Carbon::createFromDate($request->start_d);
        $end = Carbon::createFromDate($request->end_d);
        $count_day = $start->diffInDays($end);
        $count = $request->count;
        if($hotel->exely_id != null){
            $min = Room::where('hotel_id', $hotel->exely_id)->where('status', 1)->min('price');
            $rooms = Room::where('hotel_id', $hotel->exely_id)->where('status', 1)->paginate(10);
            return view('pages.hotel', compact('hotel', 'rooms', 'min', 'start', 'end', 'count', 'count_day', 'request'));
        } else{
            if ($hotel != null) {
                $min = Room::where('hotel_id', $hotel->id)->where('status', 1)->min('price');
                $rooms = Room::where('hotel_id', $hotel->id)->where('status', 1)->orderBy('price', 'asc')->paginate(10);
                return view('pages.hotel', compact('hotel', 'rooms', 'min', 'start', 'end', 'count', 'count_day', 'request'));
            } else {
                return view('pages.hotel', compact('hotel', 'start', 'end', 'count', 'count_day', 'request'));
            }
        }
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
        $related = Room::where('id', '!=', $room->id)->where('hotel_id', )->where('status', 1)->orderBy('created_at', 'DESC')->get();
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

        //cancellation
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

    public function order(Request $request)
    {
        $start = $request->start_d;
        $end = $request->end_d;
        $price = $request->price;
        $book_token = str()->random(15);
        $user = Auth::id();

        return view('pages.order', compact('request', 'start', 'end', 'price', 'book_token', 'user'));
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
