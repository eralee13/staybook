<?php

namespace App\Http\Resources\V1_1;

use App\Models\Accommodation;
use App\Models\Amenity;
use App\Models\Image;
use App\Models\Rate;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //hotels
        //images
        $images_hotel = Image::where('hotel_id', $this->id)->get();
        $images_hotel_array = [];
        foreach ($images_hotel as $file) {
            $images_hotel_array[] = [
                'category' => 'Balcon',
                'url' => $file->image,
            ];
        }

        //amenities
        $amenities = Amenity::where('hotel_id', $this->id)->get();
        $amenity_array = [];
        foreach ($amenities as $amenity) {
            $amenity_array = explode(',', $amenity->services);
        }



        //rooms
        $rooms_array = [];
        $rooms = Room::where('hotel_id', $this->id)->get();
        foreach ($rooms as $room) {
            //images
            $images_room = Image::where('room_id', $room->id)->get();
            $images_room_array = [];
            foreach ($images_room as $file) {
                $images_room_array[] = [
                    'category' => 'Hall',
                    'url' => $file->image
                ];
            }

            //room amenities
            $amenity_room_array = explode(',', $room->amenities);

            //room rates
            $rates_room = Rate::where('room_id', $room->id)->get();
            $rates_room_array = [];
            foreach ($rates_room as $rate) {
                $rates_room_array[] = [
                    'name' => $rate->bed_type,
                    'count' => $rate->adult,
                ];
            }

            $rooms_array[] = [
                'name' => $room->title,
                'id' => $room->id,
                'description' => $room->description,
                'occupancy' => $room->count,
                'area' => $room->area,
                'bed_groups' => $rates_room_array,
                'images' => $images_room_array,
                'amenities' => $amenity_room_array,
            ];

        }

        return[
            'id' => $this->id,
            'name' => $this->title,
            'description' => $this->description,
            'geo_coordinates' => [
                'latitude' => $this->lat,
                'longitude' => $this->lng,
            ],
            'address' => $this->address,
            'postal_code' => '720000',
            'contacts' => [
                'phone' => $this->phone,
                'email' => $this->email,
                'webpage' => 'https://staybook.asia'
            ],
            'currency' => 'USD',
            'stars' => $this->rating,
            'rating_certificate_info' => [
                'id' => '1234',
                'expiration_date' => '2025-10-01'
            ],
            'important_info' => 'All guests should have their passports upon arrival.',
            "check_in_instructions" => 'Do not be late.',
            'check_in_time' => $this->checkin,
            'check_in_before_time' => '',
            'check_out_time' => '',
            'images' => $images_hotel_array,
            'amenities' => $amenity_array,
            'rooms' => $rooms_array,

//            'city' => $this->city,
//            'count' => $this->count,
//            'late_out' => $this->late_out,
//            'type' => $this->type,
//            'image' => $this->image,
        ];
    }
}

