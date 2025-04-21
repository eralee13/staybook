<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\Hotel;
use App\Models\Room;


class HotelSearch extends Component
{
    
    protected $listeners = ['updateDateRange'];
    public $locale;
    public $city = 'Kyiv';
    public $dateRange;
    public $checkin;
    public $checkout;
    public $adults = 1;
    public $child;
    public $childrenage;
    public $childrenage2;
    public $childrenage3;
    public $roomCount = 1;
    public $accommodation_type = 'hotel';
    public $citizen;
    public $rating;
    public $food;
    public $early_in;
    public $early_out;
    public $cancelled;
    public $extra_place;
    public $pricemin;
    public $pricemax;

    public function __construct()
    {   
        // $this->locale = app()->getLocale();
        // $this->startDate = Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
        // $this->endDate = Carbon::now()->addMonth()->startOfMonth()->addDay()->format('Y-m-d');
        // $this->dateRange = $this->startDate .' - '. $this->endDate;
    }


    public function searchHotels()
    {   
        $this->validate([
            'dateRange' => 'required',
        ], [
            'dateRange.required' => 'Выберите диапазон дат.',
        ]);

        // Сохраняем данные в сессии
        session()->put('hotel_search', [
            'city' => $this->city,
            'dateRange' => $this->dateRange,
            'adults' => $this->adults,
            'child' => $this->child,
            'childrenage' => $this->childrenage,
            'childrenage2' => $this->childrenage2,
            'childrenage3' => $this->childrenage3,
            'roomCount' => $this->roomCount,
            'accommodation_type' => $this->accommodation_type,
            'citizen' => $this->citizen,
            'rating' => $this->rating,
            'food' => $this->food,
            'early_in' => $this->early_in,
            'early_out' => $this->early_out,
            'cancelled' => $this->cancelled,
            'extra_place' => $this->extra_place,
            'pricemin' => $this->pricemin,
            'pricemax' => $this->pricemax,
        ]);

        // Перенаправляем на другую страницу
        return redirect()->route('hotel.results');
     
    }


    public function updateDateRange($value)
    {
        $this->dateRange = $value;
    }


    public function render()
    {
        return view('livewire.hotel-search');
    }
}

