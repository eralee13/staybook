<?php

namespace App\Livewire;

use App\Models\CancellationRule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Rate;
use App\Models\Meal;
use App\Models\Image;
use App\Models\Amenity;

// Мультиформа создание отеля с номерами и тарифами

class HotelWizard extends Component
{
    use WithFileUploads;

    public $listeners = ['setCancelPolicy'];
    public $step = 1;

    // Поля формы
    public $title, $title_en, $type, $city, $address, $address_en, $lat, $lng, $timezone;
    public $checkin, $checkout, $rating, $description, $description_en, $email, $phone, $hotelError, $hotelSuccess;
    public $room_name, $selected_room, $room_name_en, $room_desc, $room_desc_en, $area, $roomError;
    public $rate, $selected_rate, $rate_name, $rate_name_en, $meal, $rateError, $availability, $bed_type, $price, $price2, $adult, $child, $children_allowed, $free_children_age, $child_extra_fee, $cancellation_rule_id, $rate_desc, $rate_desc_en;
    public $rule_name, $is_refundable, $free_cancellation_days, $penalty_type, $penalty_amount, $descr,  $ruleError;
    public $meals = [];
    public $hotel_images = [];
    public $rooms = [];
    public $rates = [];
    public $rules = [];
    public $room_images = [];
    public $services = [], $services_en = [], $room_services = [], $room_services_en = [];
    public $hotel_id = '';
    public $cancel_policy = "free_until_checkin", $status;    
    public $freeCancelBlock = false;
    public $freeThenBlock = false;
    public $nonFreeBlock = false;

    protected function rules()
    {

        if ($this->step == 1) {
            return [
                'title' => 'required|string',
                'title_en' => 'required|string',
                'type' => 'required|string',
                'city' => 'required|string',
                'timezone' => 'required|string',
                'address' => 'required|string',
                'address_en' => 'required|string',
                'lat' => 'required|numeric|between:-90,90',
                'lng' => 'required|numeric|between:-180,180',
            ];
        }

        if ($this->step == 2) {
            return [
                'checkin' => 'required|string',
                'checkout' => 'required|string',
                'rating' => 'required|integer',
                // 'description' => 'required|string',
                // 'description_en' => 'required|string',
                'email' => 'required|email', 
                'phone' => 'required',
                // 'hotel_images' => 'required|array|max:3',
                // 'hotel_images.*' => 'image|max:6144',
            ];
        }

        if ($this->step == 3) {

            if( empty($this->rooms) ){
                $this->roomError = 'Пожалуйста, добавьте хотя бы один номер перед переходом к следующему шагу.';
            }
                return [
                    // 'room_images' => 'required|array|max:5',
                    // 'room_images.*' => 'image|max:10144',
                    'room_name' => 'required|string|max:255',
                    'room_name_en' => 'required|string|max:255',
                    'area' => 'required|string|max:255',
                ];
            
        }

        if ($this->step == 4) {

            if( !empty($this->rooms) ){
                $this->roomError = '';
            }

        }

       if ($this->step == 5) {
        //    return [
        //        'selected_room' => 'required',
        //    ];
       }

        return [];
    }


    public function messages()
    {
        return [
            'selected_rate.required' => 'Пожалуйста, выберите тариф для политики отмены. Тариф создается в предыдущем окне!',
            'selected_room.required' => 'Пожалуйста, выберите номер для тарифа. Номер создается в 3 шаге!',
            // 'title' => 'required|string',
            // 'title_en' => 'required|string',
            // 'type' => 'required|string',
            // 'city' => 'required|string',
            // 'address' => 'required|string',
            // 'address_en' => 'Обязательное поле',
            'hotel_images' => 'Обязательное поле! Максимальный размер изображения для каждого файла 2MB. Максимум 3 изображений.',
            'room_images' => 'Обязательное поле! Максимальный размер изображения для каждого файла 2MB. Максимум 5 изображений.',
            'lat' => 'Кликните на карту, чтобы установить координаты отеля',
            'lng' => 'Кликните на карту, чтобы установить координаты отеля',
        ];
    }

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('index');
        }

        $this->meals = Meal::all(['id', 'title']);
    }

    

    // public function setCancelPolicy($value)
    // {   
    //     dd($value);
    //     $this->cancel_policy = $value;
    // }
    

    // public function updated()
    // {

    // }

    public function nextStep()
    {

        if ($this->step == 1 || $this->step == 2 || $this->step == 5) {

            $this->validate();

        }

        if ($this->step == 3){
            if( empty($this->rooms) ){ 
                $this->validate();
                $this->step = 3; // Возвращаемся на шаг 2, если нет номеров
            }
        }

        $this->step++;

        if ($this->step == 3) {

            $this->createHotel();

        }

        if ($this->step == 4 && $this->hotel_id) {
            $this->hotelSuccess = '';
        }
        
    }

    public function prevStep()
    {
        $this->step--;
    }

    public function firstRoom()
    {

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

            if (empty($this->hotel_id)) {

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

                if ($this->services) {

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
                $this->hotelSuccess = 'Отель успешно добавлен! Пожалуйста продолжайте добавлять номера';
                $this->hotelError = '';
            }

        } catch (\Throwable $th) {
            $this->hotelError = 'Ошибка при добавлении отеля: ' . $th->getMessage();
        }
    }

    public function addRoom()
    {
        $this->validate([
                // 'room_images' => 'required|array|max:5',
                // 'room_images.*' => 'image|max:10144',
                'room_name' => 'required|string|max:255',
                'room_name_en' => 'required|string|max:255',
                'area' => 'required|string|max:255',
            ]);


        try {

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
                'area' => $this->area,
                'description' => $this->room_desc,
                'description_en' => $this->room_desc_en,
                'amenities' => implode(',', $this->room_services),
                'amenities_en' => implode(',', $this->room_services_en),
            ]);

            if ($this->room_images) {
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
            $this->area = '';
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

    public function addRule()
    {

            if ($this->cancel_policy == 'free_then_penalty') {

                $this->validate([
                    'rule_name' => 'required|string|max:255',
                    'free_cancellation_days' => 'required|numeric|min:1',
                    'penalty_type' => 'required|string',
                    'penalty_amount' => 'required|numeric|min:1',
                ]);
            
            }

            if ($this->cancel_policy == 'free_then_penalty' || $this->cancel_policy == 'non_refundable') {

                $this->validate([
                    'rule_name' => 'required|string|max:255',
                    'penalty_type' => 'required',
                    'penalty_amount' => 'required|numeric|min:1',
                ]);
            }

            $this->validate([
                'rule_name' => 'required|string|max:255',
            //                'is_refundable' => 'required|integer',
            //                'free_cancellation_days' => 'required|integer',
            //                'penalty_type' => 'required',
            //                'penalty_amount' => 'required|integer',
            ]);


        try {

            $exists = CancellationRule::where('hotel_id', $this->hotel_id)
                ->where('title', $this->rule_name)
                ->exists();

            if ($exists) {
                $this->addError('rule_name', 'Правило с таким названием уже существует для этого номера в этом отеле.');
                return;
            }

            $rule = CancellationRule::create([
                'hotel_id' => $this->hotel_id,
                'title' => $this->rule_name,
                'is_refundable' => $this->is_refundable ?? 0,
                'free_cancellation_days' => $this->free_cancellation_days,
                'penalty_type' => $this->penalty_type ?? 'fixed',
                'cancel_policy' => $this->cancel_policy,
                'penalty_amount' => $this->penalty_amount,
                'description' => $this->descr,
            ]);

            $this->rules [$rule->id] = [
                'id' => $rule->id,
                'name' => $this->rule_name,
            ];

            $this->rule_name = '';
            $this->ruleError = '';


        } catch (\Throwable $th) {
            $this->ruleError = 'Ошибка при добавлении Политики: ' . $th->getMessage();
        }

    }

    // public function updatedCancelPolicy($value)
    // {
    //     // Скрываем всё
    //     $this->freeCancelBlock = false;
    //     $this->freeThenBlock = false;
    //     $this->nonFreeBlock = false;

    //     // Включаем нужный блок
    //     switch ($value) {
    //         case 'free_until_checkin':
    //             $this->freeCancelBlock = true;
    //             break;
    //         case 'free_then_penalty':
    //             $this->freeThenBlock = true;
    //             break;
    //         case 'non_refundable':
    //             $this->nonFreeBlock = true;
    //             break;
    //     }

    //     // Сбрасываем все зависимые поля
    //     $this->free_cancellation_days = null;
    //     $this->penalty_type = null;
    //     $this->penalty_amount = null;
    // }

    public function setCancelPolicy($value)
    {
        $this->cancel_policy = $value;
    }
    
    public function addRate()
    {

        $this->validate([
                'selected_room' => 'required',
                'rate_name_en' => 'required|string|max:255',
                'rate_name' => 'required|string|max:255',
                'cancellation_rule_id' => 'required',
            ]);

        try {

            $exists = Rate::where('rates.room_id', $this->selected_room)
                ->where('rates.hotel_id', $this->hotel_id)
                ->where('rates.title_en', $this->rate_name_en)
                ->exists();

            if ($exists) {
                $this->addError('rate_name', 'Тариф с таким названием уже существует для этого номера в этом отеле.');
                return;
            }

            $rate = Rate::create([
                'room_id' => $this->selected_room ?? null,
                'hotel_id' => $this->hotel_id,
                'title' => $this->rate_name,
                'title_en' => $this->rate_name_en,
                'meal_id' => $this->meal,
                'availability' => $this->availability ?? 0,
                'bed_type' => $this->bed_type,
                'children_allowed' => $this->children_allowed ?? '',
                'free_children_age' => $this->free_children_age ?? 0,
                'child_extra_fee' => $this->child_extra_fee ?? 0,
                'adult' => $this->adult ?? 1,
                'child' => $this->child ?? null,
                'cancellation_rule_id' => $this->cancellation_rule_id ?? null,
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

    public function render()
    {
        return view('livewire.hotel-wizard')->extends('layouts.master');
    }
}