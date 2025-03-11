<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RateResource extends JsonResource
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
            'title' => $this->title,
            'title_en' => $this->title_en,
            'hotel_id' => $this->hotel_id,
            'room_id' => $this->room_id,
            'meal_id' => $this->meal_id,
            'rule_id' => $this->rule_id,
            'price' => $this->price,
            'price2' => $this->price2,
        ];
    }
}
