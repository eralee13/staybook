<?php

namespace App\Http\Controllers\API\Exely;

use App\Http\Controllers\Controller;
use App\Models\CategoryRoom;
use App\Models\City;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\Page;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    //exely api
    public function properties()
    {
        $now = Carbon::now();
        $nowDate = Carbon::now()->format('Y-m-d');
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        if ($now->hour >= 3 && $now->hour < 4) {
            $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties');
            $properties = $response->object();
            if ($properties->properties != null) {
                foreach ($properties->properties as $hotel) {

                    $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $hotel->id);
                    $property = $response->object();

                    // hotel image
                    $url = $property->images[0]->url;
                    $imageContents = file_get_contents($url);
                    if (!$imageContents) {
                        return response()->json(['error' => 'Не удалось загрузить изображение'], 400);
                    }
                    $filename = 'hotels/' . Str::uuid() . '.jpg';
                    Storage::disk('public')->put($filename, $imageContents);

                    $exely_hotel = Hotel::where('exely_id', $property->id)->first();
                    if ($exely_hotel != null) {
                        $exely_hotel->update(
                            [
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
                            ]
                        );
                    } else{
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

                    //exely_city
                    $exely_city = City::where('exely_id', $property->contactInfo->address->cityId)->first();
                    if( $exely_city != null ){
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
//                    City::updateOrCreate(
//                        [
//                            'title' => $property->contactInfo->address->cityName,
//                            'code' => Str::slug($property->contactInfo->address->cityName),
//                        ], [
//                            'exely_id' => $property->contactInfo->address->cityId,
//                        ]
//                    );

                    //exely_room
                    if ($property->roomTypes != null) {
                        foreach ($property->roomTypes as $room) {
                            // room image
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

                            $exely_category = CategoryRoom::where('title', $room->categoryName)->first();
                            if ($exely_category == null) {
                                CategoryRoom::create(
                                    [
                                        'title' => $room->categoryName,
                                        'title_en' => $room->categoryName,
                                        'code' => Str::slug($room->categoryName),
                                    ]
                                );
                            }

                            $exely_room = Room::where('exely_id', $room->id)->first();

                            if( $exely_room != null ){
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

//                    foreach ($room->amenities as $amenity) {
//                        Room::updateOrCreate([
//                            'services' => implode(',', $amenity->name),
//                        ], [
//                            'exely_id' => $property->id,
//                        ]);
//                    }

//                            foreach ($room->images as $image) {
//                                Image::updateOrCreate(
//                                    [
//                                        'image' => $image->url,
//                                        'room_id' => $room->id,
//                                    ]
//                                );
////                            $url_room = $image->url;
////                            $contents_room = file_get_contents($url_room);
////                            $name_room = substr($url, strrpos($url_room, '/') + 1);
////                            Storage::put('rooms/' . $name_room, $contents_room);
//                            }
                        }
                    }
                }
            }
        }
        $cities = City::all();

        return view('pages.exely.properties', compact('cities', 'nowDate', 'tomorrow'));
    }

    public function property($property)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $property);
        $property = $response->object();
        dd($property);

        return view('pages.exely.property', compact('property'));
    }

    public function meals()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/meal-plans');
        $meals = $response->object();

        return view('pages.exely.meals', compact('meals'));
    }

    public function roomtypes()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/room-type-categories');
        $types = $response->object();

        return view('pages.exely.roomtypes', compact('types'));
    }

    public function amenities()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/room-amenity-categories');
        $amenities = $response->object();

        return view('pages.exely.amentities', compact('amenities'));
    }

    public function extrarules()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/500803/extra-stay-rules');
        $rules = $response->object()->extraStayRules;
        dd($rules);

        return view('pages.exely.extrarules', compact('rules'));
    }

}
