<?php

namespace App\Livewire;

use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\Image;
use App\Models\Meal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class HotelWizard extends Component
{
    use WithFileUploads;

    public $step = 1;

    public $title, $title_en, $type, $city, $address, $address_en, $lat, $lng, $timezone;
    public $checkin, $checkout, $rating, $description, $description_en, $email, $phone, $status;
    public $hotelError, $hotelSuccess;
    public $hotel_images = [];
    public $services = [], $services_en = [];
    public $hotel_id = '';
    public $meals = [];

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
                'email' => 'required|email',
                'phone' => 'required',
            ];
        }

        return [];
    }

    public function messages()
    {
        return [
            'hotel_images' => 'Обязательное поле! Максимальный размер изображения для каждого файла 2MB. Максимум 3 изображений.',
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

    public function nextStep()
    {
        $this->validate();

        $this->step++;

        if ($this->step == 2) {
            $this->createHotel();
        }
    }

    public function prevStep()
    {
        $this->step--;
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
                    ]);
                }

                if ($this->hotel_images) {
                    foreach ($this->hotel_images as $image) {
                        $path = $image->store('hotels/' . $hotel->id, 'public');
                        Image::create([
                            'hotel_id' => $hotel->id,
                            'image' => $path,
                        ]);
                    }
                }

                $this->hotel_id = $hotel->id;
                //$this->hotelSuccess = 'Отель успешно добавлен! Пожалуйста ожидайте подтверждения!';
                $this->hotelError = '';
            }
        } catch (\Throwable $th) {
            $this->hotelError = 'Ошибка при добавлении отеля: ' . $th->getMessage();
        }
    }

    public function submit()
    {
        $this->validate($this->rules());

        $this->hotelSuccess = 'Отель успешно добавлен! Пожалуйста ожидайте подтверждения!';

        session()->flash('warning', 'Отель успешно создан! Пожалуйста ожидайте подтверждения!');
        return redirect()->route('hotels.index');
    }

    public function render()
    {
        return view('livewire.hotel-wizard')->extends('layouts.master');
    }
}