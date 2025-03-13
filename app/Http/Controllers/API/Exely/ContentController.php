<?php

namespace App\Http\Controllers\API\Exely;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    //exely api
    public function properties()
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties?count=20&include=All');
        $properties = $response->object();
        if ($properties->properties != null) {
            foreach ($properties->properties as $property) {
                //dd($property);
                Hotel::updateOrCreate(
                    [
                        'title' => $property->name,
                        'title_en' => $property->name,
                        'code' => Str::slug($property->name),
                        'description' => $property->description,
                        'description_en' => $property->description,
                        'image' => $property->images[0]->url,
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
                        'status' => 1,
                    ], [
                        'exely_id' => $property->id,
                    ]
                );

                if ($property->roomTypes != null) {
                    foreach ($property->roomTypes as $room) {

                        //dd($room);
                        Room::updateOrCreate(
                            [
                                'title' => $room->name,
                                'title_en' => $room->name,
                                'code' => Str::slug($room->name),
                                'description' => $room->description,
                                'description_en' => $room->description,
                                'area' => $room->size->value,
                                'image' => $room->images[0]->url ?? 'no_image.png',
                                'hotel_id' => $property->id,
                                //'category_id' => $room->category->name,
                                'status' => 1,
                            ], [
                                'exely_id' => $room->id,
                            ]
                        );
//                    foreach ($room->amenities as $amenity) {
//                        Room::updateOrCreate([
//                            'services' => implode(',', $amenity->name),
//                        ], [
//                            'exely_id' => $property->id,
//                        ]);
//                    }

                        foreach ($room->images as $image) {
                            Image::updateOrCreate(
                                [
                                    'image' => $image->url,
                                    'room_id' => $room->id,
                                ]
                            );
                        }
                    }
                }
            }
        }
        $hotels = Hotel::all();

        return view('pages.exely.properties', compact('properties', 'hotels'));
    }

    public function property($property)
    {
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $property);
        $property = $response->object();
        //dd($property);

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
