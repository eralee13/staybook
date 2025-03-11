<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
        return[
            'code' => Str::slug($this->title),
            'id' => $this->id,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'description' => $this->description,
            'description_en' => $this->description_en,
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'city' => $this->city,
            'address' => $this->address,
            'address_en' => $this->address_en,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'email' => $this->email,
            'phone' => $this->phone,
            'count' => $this->count,
            'rating' => $this->rating,
            'early_in' => $this->early_in,
            'late_out' => $this->late_out,
            'type' => $this->type,
            'image' => $this->image,
        ];
    }
}

