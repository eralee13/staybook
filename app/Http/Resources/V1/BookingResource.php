<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->title,
            'hotel_id' => $this->hotel_id,
            'room_id' => $this->room_id,
            'title' => $this->title,
            'phone' => $this->phone,
            'email' => $this->email,
            'comment' => $this->comment,
            'adult' => $this->adult,
            'childages' => $this->childages,
            'sum' => $this->sum,
            'arrivalDate' => $this->arrivalDate,
            'departureDate' => $this->departureDate,
        ];
    }
}
