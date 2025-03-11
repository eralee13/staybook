<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccommodationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'room_id' => $this->room_id,
            'place_without' => $this->place_without,
            'place_extra' => $this->place_extra,
            'price_without' => $this->price_without,
            'price_extra' => $this->price_extra,
            'age_from' => $this->age_from,
            'age_to' => $this->age_to,
        ];
    }
}
