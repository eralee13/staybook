<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\log;
use DateTimeZone;
use DateTime;
use App\Models\Book;
use App\Models\City;
use App\Models\Contact;
use App\Models\Page;
use App\Models\Rate;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\CancellationRule;


class PageController extends Controller
{
    public function index()
    {
        $hotels = Hotel::cacheFor(now()->addHours(2))->where('tourmind_id', null)->get();
        $cities = City::orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $now = Carbon::now();
        if ($now->hour > 3 && $now->hour < 4) {

            //exely static data
            $response = Http::withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])->get(config('services.exely.base_url') . 'content/v1/properties');
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
                                'rating' => $property->stars,
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
                        $url = $property->images[0]->url ?? '';
                        $imageContents = file_get_contents($url);
                        if (!$imageContents) {
                            return response()->json(['error' => 'Не удалось загрузить изображение'], 400);
                        }
                        $filename = 'hotels/' . Str::uuid() . '.jpg';
                        Storage::disk('public')->put($filename, $imageContents);
                        Hotel::create([
                            'title' => $property->name,
                            'title_en' => $property->name,
                            'code' => Str::slug($property->name),
                            'description' => $property->description,
                            'description_en' => $property->description,
                            'image' => $filename,
                            'rating' => $property->stars,
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
            //                    if ($property->ratePlans != null) {
            //                        foreach ($property->ratePlans as $rate) {
            //                            $exely_rate = Room::where('exely_id', $room->id)->first();
            //                            if ($exely_rate != null) {
            //                                $exely_rate->update([
            //                                    'title' => $rate->name,
            //                                    'title_en' => $rate->name,
            //                                    'hotel_id' => $property->id,
            //                                    'exely_id' => $rate->id,
            //                                    'room_id' => $room->id,
            //                                    'desc_en' => $rate->description,
            //                                    'currency' => $rate->currency,
            //                                    'price' => $rate->price ?? 1,
            //                                    'cancellation_rule_id' => $rate->cancellationRuleId,
            //                                    'bed_type' => $room->name,
            //                                    'children_allowed' => $rate->isStayWithChildrenOnly,
            //                                    'availability' => 1,
            //                                    'free_children_age' => 1,
            //                                    'child_extra_fee' => 10,
            //                                ]);
            //                            } else {
            //                                Rate::create([
            //                                    'title' => $rate->name,
            //                                    'title_en' => $rate->name,
            //                                    'hotel_id' => $property->id,
            //                                    'exely_id' => $rate->id,
            //                                    'room_id' => $room->id,
            //                                    'desc_en' => $rate->description,
            //                                    'currency' => $rate->currency,
            //                                    'price' => $rate->price ?? 1,
            //                                    'cancellation_rule_id' => $rate->cancellationRuleId,
            //                                    'bed_type' => $room->name,
            //                                    'children_allowed' => $rate->isStayWithChildrenOnly,
            //                                    'availability' => 1,
            //                                    'free_children_age' => 1,
            //                                    'child_extra_fee' => 10,
            //                                ]);
            //                            }
            //                        }
            //                    }

            //                    //amenity
            //                    if ($property->roomTypes != null) {
            //                        foreach ($property->roomTypes as $amenity) {
            //                            // room image
            //                            $url_room = $room->images[0]->url ?? null;
            //                            if ($url_room != null) {
            //                                $imageRoomContents = file_get_contents($url_room);
            //                                if (!$imageRoomContents) {
            //                                    return response()->json(['error' => 'Не удалось загрузить изображение'], 400);
            //                                }
            //                                $file_room = 'rooms/' . Str::uuid();
            //                                Storage::disk('public')->put($file_room, $imageRoomContents);
            //                            } else {
            //                                $file_room = 'images/no-image.png';
            //                            }
            //
            //                            $exely_room = Room::where('exely_id', $room->id)->first();
            //
            //                            if ($exely_room != null) {
            //                                $exely_room->update([
            //                                    'title' => $room->name,
            //                                    'title_en' => $room->name,
            //                                    'code' => Str::slug($room->name),
            //                                    'description' => $room->description,
            //                                    'description_en' => $room->description,
            //                                    'area' => $room->size->value,
            //                                    'image' => 'no-image.png',
            //                                    'hotel_id' => $property->id,
            //                                    'exely_id' => $room->id,
            //                                    'category_id' => $room->categoryName,
            //                                    'status' => 1,
            //                                ]);
            //                            } else {
            //                                Room::create([
            //                                    'title' => $room->name,
            //                                    'title_en' => $room->name,
            //                                    'code' => Str::slug($room->name),
            //                                    'description' => $room->description,
            //                                    'description_en' => $room->description,
            //                                    'area' => $room->size->value,
            //                                    'image' => 'no-image.png',
            //                                    'hotel_id' => $property->id,
            //                                    'exely_id' => $room->id,
            //                                    'category_id' => $room->categoryName,
            //                                    'status' => 1,
            //                                ]);
            //                            }
            //                        }
            //                    }


                }
            }
        }
        return view('index', compact('hotels', 'cities', 'tomorrow'));

    }

    public function search(Request $request)
    {
        // dd($request);
        $cities = City::where('country_id', null)->orderBy('title', 'asc')->get();
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $query = Hotel::with(['rates' => function ($q) use ($request) {
            //    if ($request->filled('min_price')) {
            //        $q->where('price', '>=', $request->min_price);
            //    }

            //    if ($request->filled('max_price')) {
            //        $q->where('price', '<=', $request->max_price);
            //    }

            if ($request->filled('adult')) {
                $q->where('availability', '>=', $request->adult);
            }

            if ($request->filled('child')) {
                $q->where('child', '>=', $request->child);
            }

            if ($request->filled('meal_id')) {
                $q->where('meal_id', $request->meal_id);
            }

            //    if ($request->boolean('early_in')) {
            //        $q->where('early_in', true);
            //    }

            //    if ($request->boolean('late_out')) {
            //        $q->where('late_out', true);
            //    }

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

        $hotelService = new \App\Services\Tourmind\HotelServices();
        $tmhotels = $hotelService->tmGetHotels($request);
        
        return view('pages.search', compact('hotels', 'cities', 'tomorrow', 'request', 'related', 'tmhotels'));
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

        if ($_GET['api_name'] == 'TM') {
            $hotelService = new \App\Services\Tourmind\HotelServices();
            $tmroom = $hotelService->getOneDetail($request, $hotel->id);
            $tmimages = Image::where('hotel_id', $hotel->id)->where('caption', 'Room')->get('image');
        }else{
            $tmroom = [];
        }

        if ($hotel->exely_id != null) {
            return view('pages.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
        } 
        elseif ($_GET['api_name'] == 'TM') {
            return view('pages.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms', 'tmroom', 'tmimages'));
        } else {
            return view('pages.hotel', compact('hotel', 'arrival', 'departure', 'adult', 'count_day', 'request', 'rooms'));
        }

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

}
