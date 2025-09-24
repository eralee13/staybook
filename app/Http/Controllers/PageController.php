<?php

namespace App\Http\Controllers;

use App\Exceptions\EtgBadRequestException;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::where('tourmind_id', null)->where('status', 1)->latest()->limit(9)->get();
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
        $contacts = Contact::first();
        return view('pages.about', compact('page', 'request', 'contacts'));
    }

    public function service()
    {
        return view('pages.service');
    }

    public function contactspage()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 5)->first();;
        $contacts = Contact::first();
        return view('pages.contacts', compact('page', 'contacts'));
    }

    public function exely_import()
    {
        $service = new ExelyImportService();
        $service->handle();
        return view('pages.exely_import');
    }

    public function companies()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 7)->first();
        $contacts = Contact::first();
        return view('pages.page', compact('page', 'contacts'));
    }

    public function apartments()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 8)->first();
        $contacts = Contact::first();
        return view('pages.page', compact('page', 'contacts'));
    }

    public function objects()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 9)->first();
        $contacts = Contact::first();
        return view('pages.page', compact('page', 'contacts'));
    }

    public function rules()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 11)->first();
        $contacts = Contact::first();
        return view('pages.page', compact('page', 'contacts'));
    }

    public function privacy()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 12)->first();
        $contacts = Contact::first();
        return view('pages.page', compact('page', 'contacts'));
    }

    public function legal()
    {
        $page = Page::cacheFor(now()->addHours(6))->where('id', 13)->first();
        $contacts = Contact::first();
        return view('pages.page', compact('page', 'contacts'));
    }

    public function extranet()
    {
        return view('pages.extranet');
    }
    public function offline_request(Request $request)
    {
        $cities = City::where('country_id', null)->orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow();
        return view('pages.offline', compact('cities', 'tomorrow', 'request'));
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
