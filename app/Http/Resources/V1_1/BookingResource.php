<?php

namespace App\Http\Resources\V1_1;

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
            'reservation_id' => $this->id,
            'client_reference_id' => $this->book_token,
            'hotel_id' => $this->hotel_id,
            'room_id' => $this->room_id,
            'firstName' => $this->title,
            'lastName' => $this->title,
            'phone' => $this->phone,
            'email' => $this->email,
            'comment' => $this->comment,
            'adult' => $this->adult,
            //'child' => $this->child,
            'price' => $this->sum,
            'arrivalDate' => $this->arrivalDate,
            'departureDate' => $this->departureDate,
            'status' => $this->status
        ];
    }
}
