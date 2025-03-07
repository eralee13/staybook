<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'title' => $this->title,
            'checkin' => $this->checkin,
            'checkout' => $this->checkout,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'rating' => $this->rating,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'early_in' => $this->early_in,
            'early_out' => $this->early_out,
            'type' => $this->type,
        ];
    }
}

