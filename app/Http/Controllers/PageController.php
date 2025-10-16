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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageController extends Controller
{

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
                ->orWhereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
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
                        ->orWhereRaw('LOWER(title)  = ?', ['bishkek'])
                        ->orWhere('code', 'bishkek');
                })->first();

            if (!$city) {
                $city = City::whereIn('country_code',['KG','KGS'])
                    ->where(function($w) use ($qLower){
                        $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                            ->orWhereRaw('LOWER(title)  LIKE ?', ["%{$qLower}%"])
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
                        ->orWhereRaw('LOWER(title)  LIKE ?', ["%{$qLower}%"])
                        ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
                })->orderBy('title')->first();
        }

        // 4) Если страны нет — ищем город глобально
        if (!$city && $qLower !== '') {
            $city = City::where(function($w) use ($qLower){
                $w->whereRaw('LOWER(title) LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(title)  LIKE ?', ["%{$qLower}%"])
                    ->orWhereRaw('LOWER(code)  LIKE ?', ["%{$qLower}%"]);
            })->orderBy('title')->first();
            if ($city && !$country) $country = $city->country;
        }

        return [$country,$city];
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $country = null;
        $city    = null;

        // --- 0) Попытка штатного резолвера (как у вас было) ---
        try {
            [$country, $city] = $this->resolveLocation($q);
        } catch (\Throwable $e) {
            Log::warning('resolveLocation failed: '.$e->getMessage());
            $country = $country ?? null;
            $city    = $city ?? null;
        }

        // --- 1) Если резолвер город не дал — пробуем найти город по title LIKE ---
        if (!$city && $q !== '') {
            $like = '%'.mb_strtolower($q).'%';
            $city = City::whereRaw('LOWER(title) LIKE ?', [$like])->first();
            if ($city) {
                Log::debug('City resolved by LIKE', ['city_id' => $city->id, 'title' => $city->title]);
            }
        }

        // --- 2) Спец-кейсы для Бишкек/Кыргызстан/KGS (country_code = KGS) ---
        $isKgsQuery = function(string $s): bool {
            $t = mb_strtolower($s);
            return in_array($t, ['бишкек','bishkek','frunze','фрунзе','kgs','kg','кыргызстан','kyrgyzstan']);
        };

        $hotels = collect();

        if ($city) {
            // 2.1 Нормальный путь — по city_id
            $hotels = Hotel::with(['city','amenity','images'])
                ->where('city_id', $city->id)
                ->orderByDesc('rating')
                ->get();

            // 2.2 Фолбэк по строковому полю hotels.city (только если колонка существует)
            if ($hotels->isEmpty() && Schema::hasColumn('hotels','city')) {
                $variants = array_values(array_unique(array_filter([
                    $city->title,
                    'Bishkek','Бишкек','Frunze','Фрунзе',
                ])));
                $hotels = Hotel::with(['city','amenity','images'])
                    ->whereIn('city', $variants)
                    ->orderByDesc('rating')
                    ->get();
                if ($hotels->isNotEmpty()) {
                    Log::debug('Hotels loaded by legacy string city field', ['variants' => $variants]);
                }
            }
        } else {
            // 3) Город не распознан
            if ($q !== '') {
                // 3.1 Кыргызстан — фолбэк по country_code=KGS
                if ($isKgsQuery($q)) {
                    $hotels = Hotel::with(['city','amenity','images'])
                        ->whereHas('city', fn($c) => $c->where('country_code','KGS'))
                        ->orderByDesc('rating')
                        ->get();
                    Log::debug('Hotels loaded by country_code=KGS', ['count' => $hotels->count()]);
                }

                // 3.2 Если пусто, пробуем искать по названию города (whereHas city.title like)
                if ($hotels->isEmpty()) {
                    $like = '%'.mb_strtolower($q).'%';
                    $hotels = Hotel::with(['city','amenity','images'])
                        ->whereHas('city', fn($c) => $c->whereRaw('LOWER(title) LIKE ?', [$like]))
                        ->orderByDesc('rating')
                        ->get();
                    if ($hotels->isNotEmpty()) {
                        Log::debug('Hotels loaded by whereHas city.title LIKE', ['q' => $q, 'count' => $hotels->count()]);
                    }
                }

                // 3.3 Если всё ещё пусто — ищем по названию отеля
                if ($hotels->isEmpty()) {
                    $like = '%'.mb_strtolower($q).'%';
                    $hotels = Hotel::with(['city','amenity','images'])
                        ->where(function($w) use ($like) {
                            $w->whereRaw('LOWER(title) LIKE ?', [$like])
                                ->orWhereRaw('LOWER(title_en) LIKE ?', [$like]);
                        })
                        ->orderByDesc('rating')
                        ->get();
                    if ($hotels->isNotEmpty()) {
                        Log::debug('Hotels loaded by hotel title LIKE', ['q' => $q, 'count' => $hotels->count()]);
                    }
                }
            }

            // 3.4 Совсем пустой ввод — можно показать витрину
            if ($q === '' && $hotels->isEmpty()) {
                $hotels = Hotel::with(['city','amenity','images'])
                    ->orderByDesc('rating')
                    ->limit(20)
                    ->get();
                Log::debug('Fallback showcase hotels', ['count' => $hotels->count()]);
            }
        }

        $tomorrow = Carbon::tomorrow(config('app.timezone'))->format('Y-m-d');

        return view('index', [
            'request'  => $request,
            'tomorrow' => $tomorrow,
            'q'        => $q,
            'hotels'   => $hotels,
            'city'     => $city,
            'country'  => $country,
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
        $cities = City::orderBy('title', 'asc')->get();
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
