<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'code' => $this->code,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'hotel_id' => $this->hotel_id,
            'count' => $this->count,
            'description' => $this->description,
            'description_en' => $this->description_en,
            'area' => $this->area,
            'services' => $this->services,
            'price' => $this->price,
            'price2' => $this->price2,
            'bed' => $this->bed,
            'image' => $this->image,
            'category_id' => $this->category_id,
        ];
    }
}
