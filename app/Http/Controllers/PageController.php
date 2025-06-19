<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;
use App\Services\FXService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::where('tourmind_id', null)->get();
        //$hotels = Hotel::cacheFor(now()->addHours(2))->where('tourmind_id', null)->get();
        $cities = City::where('country_code', null)->orderBy('title', 'asc')->get();
        //$cities = City::cacheFor(now()->addHours(2))->where('country_id', null)->orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $now = Carbon::now();
        if ($now->hour > 3 && $now->hour < 4) {
            set_time_limit(300);
            //exely static data
            $response = Http::timeout(300)
                ->connectTimeout(5)
                ->retry(5)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->get(config('services.exely.base_url') . 'content/v1/properties');
            $properties = $response->object();
            if ($properties->properties != null) {
                foreach ($properties->properties as $hotel) {
                    $response = Http::withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])->get(config('services.exely.base_url') . 'content/v1/properties/' . $hotel->id);
                    $property = $response->object();
                    //dd($property);
                    $exely_hotel = Hotel::where('exely_id', $property->id)->get()->first();
                    if ($exely_hotel != null) {
                        $exely_hotel->update(
                            [
                                'title' => $property->name,
                                'title_en' => $property->name,
                                'code' => Str::slug($property->name),
                                'description' => $property->description,
                                'description_en' => $property->description,
                                //'image' => $filename,
                                'rating' => $property->stars ?? null,
                                'city' => $property->contactInfo->address->cityName,
                                'address' => $property->contactInfo->address->addressLine,
                                'address_en' => $property->contactInfo->address->addressLine,
                                'lat' => $property->contactInfo->address->latitude,
                                'lng' => $property->contactInfo->address->longitude,
                                'phone' => $property->contactInfo->phones[0]->phoneNumber ?? '',
                                'email' => $property->contactInfo->emails[0],
                                'checkin' => $property->policy->checkInTime,
                                'checkout' => $property->policy->checkOutTime,
                                'early_in' => '',
                                'late_out' => '',
                                'timezone' => $property->timeZone->id,
                                'status' => 1,
                                'exely_id' => $property->id,
                            ]
                        );
                    } else {
                        // hotel image
                        if(!empty($property->images)){
                            $url = $property->images[0]->url;
                            $imageContents = file_get_contents($url) ?? '';
                            if (!$imageContents) {
                                return response()->json(['error' => 'Не удалось загрузить изображение'], 400);
                            }
                            $filename = 'hotels/' . Str::uuid() . '.jpg';
                            Storage::disk('public')->put($filename, $imageContents);
                        }

                        Hotel::create([
                            'title' => $property->name,
                            'title_en' => $property->name,
                            'code' => Str::slug($property->name),
                            'description' => $property->description,
                            'description_en' => $property->description,
                            'image' => $filename ?? null,
                            'rating' => $property->stars ?? null,
                            'city' => $property->contactInfo->address->cityName,
                            'address' => $property->contactInfo->address->addressLine,
                            'address_en' => $property->contactInfo->address->addressLine,
                            'lat' => $property->contactInfo->address->latitude,
                            'lng' => $property->contactInfo->address->longitude,
                            'phone' => $property->contactInfo->phones[0]->phoneNumber ?? '',
                            'email' => $property->contactInfo->emails[0],
                            'checkin' => $property->policy->checkInTime,
                            'checkout' => $property->policy->checkOutTime,
                            'early_in' => '',
                            'late_out' => '',
                            'timezone' => $property->timeZone->id,
                            'status' => 1,
                            'exely_id' => $property->id,
                        ]);
                    }

                    //city
                    $exely_city = City::where('exely_id', $property->contactInfo->address->cityId)->first();
                    if ($exely_city != null) {
                        $exely_city->update([
                            'title' => $property->contactInfo->address->cityName,
                            'code' => Str::slug($property->contactInfo->address->cityName),
                            'exely_id' => $property->contactInfo->address->cityId,
                        ]);
                    } else {
                        City::create([
                            'title' => $property->contactInfo->address->cityName,
                            'code' => Str::slug($property->contactInfo->address->cityName),
                            'exely_id' => $property->contactInfo->address->cityId,
                        ]);
                    }

                    //room
                    if ($property->roomTypes != null) {
                        foreach ($property->roomTypes as $room) {
                            // room image
                            $urlRoom = $room->images[0]->url ?? null;

                            if ($urlRoom) {
                                $response = Http::timeout(5)->get($urlRoom);

                                if ($response->ok()) {
                                    $imageContents = $response->body();

                                    // — опционально: определяем расширение из MIME
                                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                                    $mime  = $finfo->buffer($imageContents);
                                    $map   = [
                                        'image/jpeg' => 'jpg',
                                        'image/png'  => 'png',
                                        'image/gif'  => 'gif',
                                        'image/webp' => 'webp',
                                    ];
                                    $ext = $map[$mime] ?? 'jpg';

                                    // — формируем локальное имя на основе части URL (чтобы оно было повторяемым)
                                    $basename = pathinfo(parse_url($urlRoom, PHP_URL_PATH), PATHINFO_FILENAME);
                                    $filename = "rooms/{$basename}.{$ext}";

                                    $disk = Storage::disk('public');
                                    if (! $disk->exists($filename)) {
                                        // Сохраняем только если файл с таким именем ещё не лежит в хранилище
                                        $disk->put($filename, $imageContents);
                                    }
                                } else {
                                    // 404 или другая ошибка — берём плейсхолдер
                                    $filename = 'images/no-image.png';
                                }
                            } else {
                                $filename = 'images/no-image.png';
                            }

                            $exely_room = Room::where('exely_id', $room->id)->first();
                            $amenities_array = [];

                            foreach($room->amenities as $amenity) {
                                $amenities_array[] = $amenity->name;
                            }

                            if ($exely_room != null) {
                                $exely_room->update([
                                    'title' => $room->name,
                                    'title_en' => $room->name,
                                    'code' => Str::slug($room->name),
                                    'description' => $room->description,
                                    'description_en' => $room->description,
                                    'area' => $room->size->value,
                                    'image' => $filename,
                                    'hotel_id' => $property->id,
                                    'exely_id' => $room->id,
                                    'category_id' => $room->categoryName,
                                    'amenities' => implode(',', $amenities_array),
                                    'status' => 1
                                ]);
                            } else {
                                Room::create([
                                    'title' => $room->name,
                                    'title_en' => $room->name,
                                    'code' => Str::slug($room->name),
                                    'description' => $room->description,
                                    'description_en' => $room->description,
                                    'area' => $room->size->value,
                                    'image' => $filename,
                                    'hotel_id' => $property->id,
                                    'exely_id' => $room->id,
                                    'category_id' => $room->categoryName,
                                    'amenities' => implode(',', $amenities_array),
                                    'status' => 1
                                ]);
                            }
                        }
                    }

                    //rate
                    if ($property->ratePlans != null) {
                        foreach ($property->ratePlans as $rate) {
                            $exely_rate = Room::where('exely_id', $room->id)->first();
                            if ($exely_rate != null) {
                                $exely_rate->update([
                                    'title' => $rate->name,
                                    'title_en' => $rate->name,
                                    'hotel_id' => $property->id,
                                    'exely_id' => $rate->id,
                                    'room_id' => $room->id,
                                    'desc_en' => $rate->description,
                                    'currency' => $rate->currency,
                                    'price' => $rate->price ?? 1,
                                    'cancellation_rule_id' => $rate->cancellationRuleId,
                                    'bed_type' => $room->name,
                                    'children_allowed' => $rate->isStayWithChildrenOnly,
                                    'availability' => 1,
                                    'free_children_age' => 1,
                                    'child_extra_fee' => 10,
                                ]);
                            } else {
                                Rate::create([
                                    'title' => $rate->name,
                                    'title_en' => $rate->name,
                                    'hotel_id' => $property->id,
                                    'exely_id' => $rate->id,
                                    'room_id' => $room->id,
                                    'desc_en' => $rate->description,
                                    'currency' => $rate->currency,
                                    'price' => $rate->price ?? 1,
                                    'cancellation_rule_id' => $rate->cancellationRuleId,
                                    'bed_type' => $room->name,
                                    'children_allowed' => $rate->isStayWithChildrenOnly,
                                    'availability' => 1,
                                    'free_children_age' => 1,
                                    'child_extra_fee' => 10,
                                ]);
                            }
                        }
                    }

                    //amenity
                    if ($property->roomTypes != null) {
                        foreach ($property->roomTypes as $amenity) {
                            // room image
                            $url_room = $room->images[0]->url ?? null;
                            if ($url_room != null) {
                                $imageRoomContents = file_get_contents($url_room);
                                if (!$imageRoomContents) {
                                    return response()->json(['error' => 'Не удалось загрузить изображение'], 400);
                                }
                                $file_room = 'rooms/' . Str::uuid();
                                Storage::disk('public')->put($file_room, $imageRoomContents);
                            } else {
                                $file_room = 'images/no-image.png';
                            }

                            $exely_room = Room::where('exely_id', $room->id)->first();

                            if ($exely_room != null) {
                                $exely_room->update([
                                    'title' => $room->name,
                                    'title_en' => $room->name,
                                    'code' => Str::slug($room->name),
                                    'description' => $room->description,
                                    'description_en' => $room->description,
                                    'area' => $room->size->value,
                                    'image' => 'no-image.png',
                                    'hotel_id' => $property->id,
                                    'exely_id' => $room->id,
                                    'category_id' => $room->categoryName,
                                    'status' => 1,
                                ]);
                            } else {
                                Room::create([
                                    'title' => $room->name,
                                    'title_en' => $room->name,
                                    'code' => Str::slug($room->name),
                                    'description' => $room->description,
                                    'description_en' => $room->description,
                                    'area' => $room->size->value,
                                    'image' => 'no-image.png',
                                    'hotel_id' => $property->id,
                                    'exely_id' => $room->id,
                                    'category_id' => $room->categoryName,
                                    'status' => 1,
                                ]);
                            }
                        }
                    }


                }
            }
        }
        return view('index', compact('hotels', 'cities', 'tomorrow'));
    }

    public function hotels()
    {
        $hotels = Hotel::where('status', 1)->paginate(21);
        return view('pages.hotels', compact('hotels'));
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

}
