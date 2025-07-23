<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfflineRequest;
use App\Mail\OfflineMail;
use App\Models\Amenity;
use App\Models\Image;
use App\Models\Offline;
use App\Services\ExelyImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Hotel;
use App\Services\FXService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::where('tourmind_id', null)->latest()->limit(9)->get();
        $cities = City::orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        //hotelstar
//        $hotelStar = new \App\Services\HotelStarService();
//
//        $searchData = [
//            'region_id' => 67005,
//            'check_in' => '2025-08-10',
//            'check_out' => '2025-08-13',
//            'adults' => 2,
//            'children' => [],
//            'currency' => 'RUB',
//            '3d_hotelstar' => '7705857799',
//        ];
//
//        $offers = $hotelStar->search($searchData);
//        $offer = $offers[0];
//
//// Актуализация
//        $actualOffer = $hotelStar->actualize($searchData, $offer);
//
//// Бронирование
//        $booking = $hotelStar->book([
//            'partner_order_id' => 'order_12345',
//            'partner_price' => $actualOffer['search_item']['price'],
//            'email' => 'client@example.com',
//            'phone' => '+79991234567',
//            'persons' => [
//                ['name' => 'Ivan', 'surname' => 'Ivanov'],
//            ],
//            'search_data' => $searchData,
//            'search_item' => [
//                'hash' => $offer['hash'],
//                'provider_id' => $offer['provider_id'],
//            ],
//            'meals' => $actualOffer['search_item']['meals'] ?? [],
//            'extras' => $actualOffer['search_item']['extras'] ?? [],
//        ]);

        return view('index', compact('hotels', 'cities', 'tomorrow'));
    }

    public function hotels()
    {
        $hotels = Hotel::where('status', 1)->latest()->paginate(27);
        return view('pages.hotels', compact('hotels'));
    }

    public function hotel($code)
    {
        $hotel = Hotel::where('code', $code)->firstOrFail();
        $images = Image::where('hotel_id', $hotel->id)->get();
        $amenities = Amenity::where('hotel_id', $hotel->id)->get();
        return view('pages.hotel', compact('hotel', 'images', 'amenities'));
    }

    public function about(Request $request)
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 4)->first();
        return view('pages.about', compact('page', 'request'));
    }

    public function contactspage()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 5)->first();;
        $contacts = Contact::get();

        $service = new ExelyImportService();
        $service->handle();

        return view('pages.contacts', compact('page', 'contacts'));
    }

    public function companies()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 7)->first();
        return view('pages.page', compact('page'));
    }

    public function apartments()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 8)->first();
        return view('pages.page', compact('page'));
    }

    public function objects()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 9)->first();
        return view('pages.page', compact('page'));
    }

    public function aboutus()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 10)->first();
        return view('pages.page', compact('page'));
    }

    public function rules()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 11)->first();
        return view('pages.page', compact('page'));
    }

    public function privacy()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 12)->first();
        return view('pages.page', compact('page'));
    }

    public function legal()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 13)->first();
        return view('pages.page', compact('page'));
    }

    public function extranet()
    {
        return view('pages.extranet');
    }
    public function offline_request()
    {
        $cities = City::where('country_id', null)->orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow();
        return view('pages.offline', compact('cities', 'tomorrow'));
    }

    public function offline_send(OfflineRequest $request)
    {
        $params = $request->except(['meal', 'childAges', 'file']);

        $params['meal'] = $request->has('meal') ? implode(',', $request->meal) : null;
        $params['childAges'] = $request->has('childAges') ? json_encode($request->childAges) : null;

        if ($request->hasFile('file')) {
            $params['file'] = $request->file('file')->store('offline_files', 'public');
        }

        $offline = Offline::create($params);
        $email = Contact::first()->email;
        Mail::to($email)->send(new OfflineMail($offline));

        session()->flash('success', 'Offline-request ' . $request->name . ' is created');
        return redirect()->route('index');
    }

}
