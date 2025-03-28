<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Hotel;
use App\Models\Room;

class HotelSearch extends Component
{
    
    public $city;
    public $dateRange;
    public $checkin;
    public $checkout;
    public $adults = 1;
    public $child;
    public $childrenage;
    public $citizen;
    public $rating;
    public $food;


    public function searchHotels()
    {   
        // Сохраняем данные в сессии
        session()->put('hotel_search', [
            'city' => $this->city,
            'dateRange' => $this->dateRange,
            'adults' => $this->adults,
            'child' => $this->child,
            'childrenage' => $this->childrenage,
            'citizen' => $this->citizen,
            'rating' => $this->rating,
            'food' => $this->food,
        ]);

        // Перенаправляем на другую страницу
        return redirect()->route('hotel.results');
     
    }

    public function render()
    {
        return view('livewire.hotel-search');
    }
}

