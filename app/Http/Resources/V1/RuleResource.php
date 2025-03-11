<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RuleResource extends JsonResource
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
            'size' => $this->id,
            'percent_book' => $this->percent_book,
            'percent_day' => $this->percent_day,
            'date_day' => $this->date_day,
            'date_book' => $this->date_book,
        ];
    }
}
