<?php

namespace App\Http\Resources\V1_1;

use App\Models\Accommodation;
use App\Models\Amenity;
use App\Models\Image;
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
        $amenities = Amenity::where('hotel_id', $this->id)->get();
        $amenity_array = [];
        foreach ($amenities as $amenity) {
            $amenity_array = explode(',', $amenity->services);
        }

        //rooms
        $rooms = Room::where('hotel_id', $this->id)->get();
        $rooms_array = [];
        foreach ($rooms as $room) {
            //images
            $images_room = Image::where('room_id', $room->id)->get();
            $images_room_array = [];
            foreach ($images_room as $file) {
                $images_room_array[] = [
                    'url' => $file->image,
                ];
            }
            //room amenities
            $amenity_room_array = explode(',', $room->services);

            $rooms_array[] = [
                'id' => $room->id,
                'title' => $room->title,
                'description' => $room->description,
                'occupancy' => $room->count,
                'area' => $room->area,
                'bed_groups' => [
                    'name' => $room->bed,
                ],
                'images' => $images_room_array,
                'amenities' => $amenity_room_array,
                //'price' => $room->price,
            ];

        }

        //accommodation
        $accommodation_array = [];
        $accommodations = Accommodation::where('hotel_id', $this->id)->get();
        foreach ($accommodations as $accommodation) {
            $accommodation_array[] = [
                'type' => '',
                'description' => '',
                //'id' => $accommodation->id,
                'price' => [
                    'currency' => 'USD',
                    'amount' => 0,
                    'is_percentage' => false,
                ],
                'price_unit' => 'single_payment',
                'payment_type' => 'any',
                'is_included' => false,
                'applicable_ages' => [
                    'from' => 0,
                    'to' => 14,
                ]

            ];
        }

        return[
            'code' => Str::slug($this->title),
            'id' => $this->id,
            'name' => $this->title,
            'description' => $this->description,
            'geo_coordinates' => [
                'latitude' => $this->lat,
                'longitude' => $this->lng,
            ],
            'address' => $this->address,
            'contacts' => [
                'phone' => $this->phone,
                'email' => $this->email,
            ],
            'currency' => 'USD',
            'stars' => $this->rating,
            'important_info' => 'All guests should have their passports upon arrival.',
            "check_in_instructions" => 'Do not be late.',
            'check_in_time' => $this->checkin,
            'check_in_before_time' => $this->early_in,
            'check_out_time' => $this->checkout,
            'images' => [
                'url' => Storage::url($this->image),
            ],
            'amenities' => $amenity_array,
            'rooms' => [
                $rooms_array,
            ],
            'extras' => [
                $accommodation_array
            ]
//            'city' => $this->city,
//            'count' => $this->count,
//            'late_out' => $this->late_out,
//            'type' => $this->type,
//            'image' => $this->image,
        ];
    }
}

