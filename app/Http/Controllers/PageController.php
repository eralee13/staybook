<?php

namespace App\Http\Controllers;

use App\Exceptions\EtgBadRequestException;
use App\Http\Requests\OfflineRequest;
use App\Mail\OfflineMail;
use App\Models\Amenity;
use App\Models\Country;
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
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function suggest(Request $request)
    {
        $q = Str::lower(trim((string)$request->get('q','')));
        if ($q === '') return response()->json([]);

        $countries = Country::selectRaw("id, name, alpha2, 'country' as type")
            ->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
            ->orWhere('alpha2', Str::upper($q))
            ->orWhere('code', Str::upper($q))
            ->limit(5)->get();

        $cities = City::selectRaw("id, title as name, country_code, 'city' as type")
            ->whereRaw('LOWER(title) LIKE ?', ["%{$q}%"])
            ->orWhereRaw('LOWER(name) LIKE ?', ["%{$q}%"])
            ->orWhereRaw('LOWER(code) LIKE ?', ["%{$q}%"])
            ->limit(10)->get();

        return response()->json($countries->concat($cities)->values());
    }

    private function resolveLocation(string $q = null): array
    {
        $qLower = Str::lower($q ?? '');
        $country = null; $city = null;

        $norm2 = function (?string $code) {
            if (!$code) return null; $u = Str::upper($code);
            return $u === 'KGS' ? 'KG' : $u; // спец-кейс KGS→KG
        };

        // 1) Если ввели alpha2/alpha3/название страны
        if ($qLower !== '') {
            $country = Country::query()
                ->where('alpha2', $norm2($qLower))
                ->orWhere('code', Str::upper($qLower))
                ->orWhereRaw('LOWER(name) LIKE ?', ["%{$qLower}%"])
                ->first();
        }

        // 2) Явный Бишкек (ru/en/ошибки ввода)
        $isBishkek = Str::contains($qLower, ['bishkek','бишкек','biskek','bishk']);
        if ($isBishkek) {
            $city = City::query()
                ->when($country, fn($w)=>$w->where('country_id',$country->id),
                    fn($w)=>$w->whereIn('country_code',['KG','KGS']))
                ->where(function($w){
                    $w->whereRaw('LOWER(title) = ?', ['bishkek'])
                        ->orWhereRaw('LOWER(title) = ?', ['бишкек'])
                        ->orWhereRaw('LOWER(name)  = ?', ['bishkek'])
                        ->orWhere('code', 'bishkek');
                })->first();

            if (!$city) {
                $city = City::whereIn('country_code',['KG','KGS'])
                    ->where(function($w) use ($qLower){
                        $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                            ->orWhereRaw('LOWER(name)  LIKE ?', ["%{$qLower}%"])
                            ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
                    })->first();
            }

            if ($city && !$country) $country = $city->country;
            return [$country,$city];
        }

        // 3) Если страна определена и строка похожа на город этой страны
        if ($country && $qLower !== '') {
            $city = City::where('country_id',$country->id)
                ->where(function($w) use ($qLower){
                    $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                        ->orWhereRaw('LOWER(name)  LIKE ?', ["%{$qLower}%"])
                        ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
                })->orderBy('title')->first();
        }

        // 4) Если страны нет — ищем город глобально
        if (!$city && $qLower !== '') {
            $city = City::where(function($w) use ($qLower){
                $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(name)  LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
            })->orderBy('title')->first();
            if ($city && !$country) $country = $city->country;
        }

        return [$country,$city];
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $country = null; $city = null;

        // --- резолвер ---
        [$country, $city] = $this->resolveLocation($q);

        // Отели строго по city_id (если нашли город)
        $hotels = collect();
        if ($city) {
            $hotels = Hotel::where('city_id', $city->id)->get();

            // fallback для старых данных без city_id — по строковому названию:
            if ($hotels->isEmpty()) {
                $hotels = Hotel::whereIn('city', array_filter([$city->title, $city->name, 'Bishkek','Бишкек']))->get();
            }
        }
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        return view('index', [
            'request' => $request,
            'tomorrow' => $tomorrow,
            'q'      => $q,
            'hotels' => $hotels,
            'city'   => $city,
            'country'=> $country,
        ]);
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
