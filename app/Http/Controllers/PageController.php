<?php

namespace App\Http\Controllers;

use App\Http\Requests\MultiForm\HotelOneRequest;
use App\Http\Requests\MultiForm\HotelTwoRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Food;
use App\Models\Image;
use App\Models\Page;
use App\Models\Room;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::all();
        $rooms = Room::where('status', 1)->inRandomOrder()->paginate(40);
        $foods = Food::all();
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties?count=20&include=All');
        $properties = $response->object()->properties;
        return view('index', compact('hotels', 'rooms', 'foods', 'properties'));
    }

    public function hotels()
    {
        $hotels = Hotel::orderBy('top', 'DESC')->paginate(30);
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties?count=20&include=All');
        $properties = $response->object()->properties;
        return view('pages.hotels', compact('hotels', 'properties'));
    }

    public function hotel($code, Request $request)
    {
        $hotel = Hotel::where('code', $code)->first();
        $start = Carbon::createFromDate($request->start_d);
        $end = Carbon::createFromDate($request->end_d);
        $count_day = $start->diffInDays($end);
        $count = $request->count;
        if($hotel != null){
            $min = Room::where('hotel_id', $hotel->id)->where('status', 1)->min('price');
            $rooms = Room::where('hotel_id', $hotel->id)->where('status', 1)->orderBy('price', 'asc')->paginate(10);
            return view('pages.hotel', compact('hotel', 'rooms', 'min', 'start', 'end', 'count', 'count_day', 'request'));
        } else {
            return view('pages.hotel', compact('hotel', 'start', 'end', 'count', 'count_day', 'request'));
        }

    }

    public function allrooms()
    {
        $rooms = Room::where('status', 1)->orderBy('price', 'asc')->paginate(30);
        return view('pages.rooms', compact('rooms'));
    }

    public function room($hotel, $roomCode)
    {
        $room = Room::withTrashed()->byCode($roomCode)->firstOrFail();
        $random = random_int(100000, 999999);
        $images = Image::where('room_id', $room->id)->get();
        //$related = Room::where('id', '!=', $room->id)->where('hotel_id', $room->hotel_id)->where('status', 1)->orderBy('price', 'asc')->get();
        $related = Room::where('id', '!=', $room->id)->where('status', 1)->orderBy('created_at', 'DESC')->get();
        return view('pages.room', compact('room', 'images', 'related', 'random'));
    }

    public function search(Request $request)
    {
        $query = Category::with('hotel', 'room', 'food', 'rule');

        //hotel
        if ($request->filled('title')) {
            $title = (array)$request->input('title');
            $query->whereHas('hotel', function ($quer) use ($title) {
                $quer->where('hotel_id', $title);
            });
        }

        //count
        if ($request->filled('count')) {
            $count = (array)$request->input('count');
            $query->whereHas('room', function ($quer) use ($count) {
                $quer->where('price2', '!=', null);
            });
        }

        //child
//        if ($request->filled('countc')) {
//            $countc = (array) $request->input('countc');
//            $query->whereHas('child', function ($quer) use ($countc) {
//                $quer->where('age1', '!=', 0);
//            });
//        }

        //date
//        if ($request->filled('start_d')) {
//            $start_d = (array) $request->input('start_d');
//            $query->whereHas('hotel', function ($quer) use ($start_d) {
//                $quer->where('start_d', $start_d);
//            });
//        }

        //rating
        if ($request->filled('rating')) {
            $rating = (array)$request->input('rating');
            $query->whereHas('hotel', function ($quer) use ($rating) {
                $quer->where('rating', $rating);
            });
        }

        //food
        if ($request->filled('food_id')) {
            $food = (array)$request->input('food_id');
            $query->whereHas('food', function ($quer) use ($food) {
                $quer->where('food_id', $food);
            });
        }

        //early
        if ($request->filled('early_in')) {
            $early_in = (array)$request->input('early_in');
            $query->whereHas('hotel', function ($quer) use ($early_in) {
                $quer->where('early_in', $early_in);
            });
        }

        //late
        if ($request->filled('early_out')) {
            $early_out = (array)$request->input('early_out');
            $query->whereHas('hotel', function ($quer) use ($early_out) {
                $quer->where('early_out', $early_out);
            });
        }

        //cancellation
        if ($request->filled('cancelled')) {
            $cancel = (array)$request->input('cancelled');
            $query->whereHas('rule', function ($quer) use ($cancel) {
                $quer->where('size', 0);
            });
        }

        //extra
//        if ($request->filled('extra_place')) {
//            $extra_place = (array) $request->input('extra_place');
//            $query->whereHas('rule', function ($quer) use ($extra_place) {
//                $quer->where('extra_place', '!=', 0);
//                $quer->orWhere('extra_place', '!=', null);
//            });
//        }

        $categories = $query->get();

        $contacts = Contact::get();

//        if ($request->filled('daterange')) {
//            $query->whereBetween('price',[$request->left_value, $request->right_value]);
//        }

        $relrooms = Room::all();

        return view('pages.search', compact('categories', 'contacts', 'request', 'relrooms'));
    }

    public function about()
    {
        $page = Page::query()->where('id', 4)->first();
        return view('pages.about', compact('page'));
    }

    public function contactspage()
    {
        $page = Page::query()->where('id', 5)->first();;
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



    public function testpage()
    {
        $hotels = Hotel::all();
        //$rooms = Room::where('status', 1)->orderBy('created_at', 'DESC')->paginate(40);
        $hotels = Hotel::where('status', 1)->orderBy('created_at', 'DESC')->paginate(40);
        $foods = Food::all();
        return view('pages.testpage', compact('hotels', 'hotels', 'foods'));
    }



    public function searchtest(Request $request)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties?count=20&include=All');
        $properties = $response->object()->properties;

        $query = Hotel::with('categories', 'food', 'rooms');
        $start = Carbon::createFromDate($request->start_d);
        $end = Carbon::createFromDate($request->end_d);
        $count_day = $start->diffInDays($end);
        $count = $request->count;

        //title
        if ($request->filled('title')) {
            $title = $request->input('title');
            $query->where('id', $title);
            //$query->orWhere('address', '%like%', $title);
            $properties =  collect($properties)->where('id', $title)->all();
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

        return view('pages.order', compact('request', 'start', 'end', 'price'));
    }


}
