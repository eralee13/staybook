<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfflineRequest;
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

class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::where('tourmind_id', null)->latest()->limit(9)->get();
        $cities = City::orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

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

        Offline::create($params);

        session()->flash('success', 'Offline-request ' . $request->name . ' is created');
        return redirect()->route('index');
    }


}
