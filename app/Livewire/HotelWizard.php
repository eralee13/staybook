<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Rate;
use App\Models\Rule;
use App\Models\Meal;
use App\Models\Image;
use App\Models\Amenity;


class HotelWizard extends Component
{
    use WithFileUploads;

    public $step = 1;

    // Поля формы
    public $title, $title_en, $type, $city, $address, $address_en, $lat, $lng;
    public $checkin, $checkout, $rating, $description, $description_en, $email, $phone, $hotelError, $hotelSuccess;
    public $room_name, $selected_room, $room_name_en, $room_desc, $room_desc_en, $roomError;
    public $rate, $selected_rate, $rate_name, $rate_name_en, $rate_desc, $rate_desc_en, $meal, $rateError;
    public $rule_name, $rule_name_en, $ruleError;
    public $meals = [];
    public $hotel_images = [];
    public $rooms = [];
    public $rates = [];
    public $rules = [];
    public $room_images = [];
    public $services = [], $services_en = [], $room_services = [], $room_services_en = [];
    public $hotel_id = '';

    protected function rules(){

        if ($this->step == 1) {
            return [
                'title' => 'required|string', 'title_en' => 'required|string', 'type' => 'required|string',
                'city' => 'required|string', 'address' => 'required|string', 'address_en' => 'required|string',
                'lat' => 'required|numeric|between:-90,90', 'lng' => 'required|numeric|between:-180,180',
            ];
        }

        if ($this->step == 2) {
            return [
                'checkin' => 'required|string', 
                'checkout' => 'required|string', 
                'rating' => 'required|integer', 
                'description' => 'required|string', 
                'description_en' => 'required|string',
                // 'email' => 'required|email', 'phone' => 'required',
                // 'hotel_images.*' => 'image|max:5048',
            ];
        }
        
        // if ($this->step == 3) {
        //     return [
        //         // 'room_name' => 'required|string', 
        //         // 'room_name_en' => 'required|string', 
        //         // 'room_desc' => 'required|string', 
        //         'room_desc_en' => 'required|string', 
        //         // 'room_images.*' => 'image|max:5048', // Максимальный размер 5MB
        //     ];
        // }

        if ($this->step == 5) {
            return [
                'rule_name_en' => 'required|string|max:255',
            ];
        }

        return [];
    }
    // protected $rules = [
    //     // 3 => ['photos.*' => 'image|max:5048'],
    // ];

    public function messages()
    {
        return [
            'selected_rate.required' => 'Пожалуйста, выберите тариф для политики отмены. Тариф создается в предыдущем окне!',
            'selected_room.required' => 'Пожалуйста, выберите номер для тарифа. Номер создается в предыдущем окне!',
        ];
    }

    public function mount()
    {
        if( !Auth::check() ){
            return redirect()->route('index');
        }

        $this->meals = Meal::all(['id', 'title']);
    }

    public function render()
    {
        return view('livewire.hotel-wizard')->extends('layouts.master');
    }

    public function nextStep()
    {   
        
        if ($this->step == 1 || $this->step == 2 || $this->step == 5) {

            $this->validate();

        }

        $this->step++;

        if ($this->step == 3) {

            $this->createHotel();
            
        }

        if ($this->step == 4 && $this->hotel_id){
            $this->hotelSuccess = '';
        }
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function firstRoom(){

        $existsDesc = Room::where('hotel_id', $this->hotel_id)
                    ->where('description_en', null)
                    ->exists();

            if (!$existsDesc) {
                Room::create(
                    [
                        'hotel_id' => $this->hotel_id,
                        'description' => $this->room_desc,
                        'description_en' => $this->room_desc_en,
                        'services' => implode(',', $this->room_services),
                    ]
                );
            }
    

            foreach ($this->room_images as $image) {
                $path = $image->store('rooms/', 'public');
            
                Image::create([
                    'hotel_id' => $this->hotel_id,
                    'image' => $path,
                ]);
            }
            
    }

    public function createHotel()
    {
        try {

            if ( empty($this->hotel_id) ){

                $hotel = Hotel::create([
                    'user_id' => Auth::id(),
                    'code' => '',
                    'title' => $this->title,
                    'title_en' => $this->title_en,
                    'checkin' => $this->checkin,
                    'checkout' => $this->checkout,
                    'rating' => $this->rating,
                    'description' => $this->description,
                    'description_en' => $this->description_en,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'type' => $this->type,
                    'city' => $this->city,
                    'address' => $this->address,
                    'address_en' => $this->address_en,
                    'lat' => $this->lat,
                    'lng' => $this->lng,
                ]);

                    if ( $this->services ) {
                        
                            Amenity::create([
                                'hotel_id' => $hotel->id,
                                'title' => 'Services',
                                'services_en' => implode(',', $this->services),
                                // 'services_en' => implode(',', $this->services_en),
                            ]);
                        
                    }

                    if ($this->hotel_images) {
                        
                        foreach ($this->hotel_images as $image) {
                            $path = $image->store('hotels/' . $hotel->id, 'public');
                        
                            Image::create([
                                'hotel_id' => $hotel->id,
                                // 'room_id' => null,
                                // 'category' => null,
                                // 'caption' => null,
                                'image' => $path,
                            ]);
                        }
                    }

                $this->hotel_id = $hotel->id;
                $this->hotelSuccess = 'Отель успешно добавлен! Продолжайте добавлять номера и т.д.';
                $this->hotelError = '';
            }

        } catch (\Throwable $th) {
            $this->hotelError = 'Ошибка при добавлении отеля: ' . $th->getMessage();
        }
    }

    public function addRoom()
    {
        try {

            $this->validate([
                'room_name_en' => 'required|string|max:255',
            ]);
    
            $exists = Room::where('hotel_id', $this->hotel_id)
                        ->where('title_en', $this->room_name_en)
                        ->exists();
    
                if ($exists) {
                    $this->addError('room_name', 'Номер с таким названием уже существует для этого отеля.');
                    return;
                }
    
            $room = Room::create([
                'hotel_id' => $this->hotel_id,
                'title' => $this->room_name,
                'title_en' => $this->room_name_en,
                'description' => $this->room_desc,
                'description_en' => $this->room_desc_en,
                // 'services' => implode(',', $this->room_services),
                'services' => implode(',', $this->room_services_en),
            ]);

                if ( $this->room_images ){

                    foreach ($this->room_images as $image) {
                        $path = $image->store('rooms/' . $room->id, 'public');
                    
                        Image::create([
                            'hotel_id' => $this->hotel_id,
                            'room_id' => $room->id,
                            'image' => $path,
                        ]);
                    }
                }
    
                $this->rooms[$room->id] = [
                    'id' => $room->id,
                    'name' => $this->room_name,
                    'name_en' => $this->room_name_en
                ];
    
                $this->room_name = '';
                $this->room_name_en = '';
                $this->room_desc = '';
                $this->room_desc_en = '';
                $this->room_images = [];
                $this->room_services = [];
                $this->room_services_en = [];
                $this->roomError = '';

        } catch (\Throwable $th) {
            $this->roomError = 'Ошибка при добавлении номера: ' . $th->getMessage();
        }

    }

    public function addRate()
    {
        try{

            $this->validate([
                'rate_name_en' => 'required|string|max:255',
                'selected_room' => 'required|integer',
            ]);
    
                $exists = Rate::where('rates.room_id', $this->selected_room)
                    ->where('rates.hotel_id', $this->hotel_id)
                    ->where('rates.title_en', $this->rate_name_en)
                    ->exists();
        
                    if ($exists) {
                        $this->addError('rate_name', 'Тариф с таким названием уже существует для этого номера в этом отеле.');
                        return;
                    }
    
                    $rate = Rate::create([
                        'room_id' => $this->selected_room,
                        'hotel_id' => $this->hotel_id,
                        'title' => $this->rate_name,
                        'title_en' => $this->rate_name_en,
                        'meal_id' => $this->meal,
                    ]);
    
                        $this->rates [$rate->id] = [
                            'id' => $rate->id,
                            'name' => $this->rate_name,
                            'name_en' => $this->rate_name_en,
                            'room_id' => $this->selected_room,
                        ];
    
            $this->rate_name = '';
            $this->rate_name_en = '';
            $this->meal = '';
            $this->rateError = '';
            $this->selected_room = '';

        } catch (\Throwable $th) {
            $this->rateError = 'Ошибка при добавлении Тарифа: ' . $th->getMessage();
        }
        
    }

    public function addRule()
    {
        try{

            $this->validate([
                'rule_name_en' => 'required|string|max:255',
                'selected_rate' => 'required|integer',
            ]);
    
                $exists = Rule::where('rules.rate_id', $this->selected_rate)
                    ->where('rules.hotel_id', $this->hotel_id)
                    ->where('rules.title_en', $this->rule_name_en)
                    ->exists();
        
                if ($exists) {
                    $this->addError('rule_name_en', 'Правило с таким названием уже существует для этого номера в этом отеле.');
                    return;
                }
    
                    $rule = Rule::create([
                        'rate_id' => $this->selected_rate,
                        'hotel_id' => $this->hotel_id,
                        'title' => $this->rule_name,
                        'title_en' => $this->rule_name_en,
                    ]);
    
                        $this->rules [$rule->id] = [
                            'id' => $rule->id,
                            'name' => $this->rule_name,
                            'name_en' => $this->rule_name_en,
                            'rate_id' => $this->selected_rate,
                        ];
    
            $this->rule_name = '';
            $this->rule_name_en = '';   
            $this->selected_rate = '';
            $this->ruleError = '';


        } catch (\Throwable $th) {
            $this->ruleError = 'Ошибка при добавлении Политики: ' . $th->getMessage();
        }
        
    }

    // Не используется
    public function submit()
    {
        
        $this->validate($this->rules[$this->step] ?? []);

        // Пример сохранения
        // Hotel::create([
        //     'name' => $this->name,
        //     'country' => $this->country,
        //     'email' => $this->email,
        //     'phone' => $this->phone,
        // ]);

         // Загрузка фотографий
        //  foreach ($this->photos as $photo) {
        //     $path = $photo->store('hotels/photos', 'public');
        //     $hotel->photos()->create(['path' => $path]);
        // }

        session()->flash('success', 'Отель успешно создан!');
        return redirect()->to('/admin/hotels');
    }
}
