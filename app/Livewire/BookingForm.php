<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;

class BookingForm extends Component
{
    public $hotel;
    public $room;
    public $checkIn;
    public $checkOut;
    public $totalPrice;
    public $token;
    public $bookingSuccess = null;

    public function mount()
    {
        $booking = session('booking');
        if ($booking) {
            $this->hotel = $booking['hotel'];
            $this->room = $booking['room'];
            $this->checkIn = $booking['checkIn'];
            $this->checkOut = $booking['checkOut'];
            $this->totalPrice = $booking['totalPrice'];
            $this->token = $booking['token'];
        }
    }

    public function confirmBooking()
    {
        if ($this->token) {
            $this->bookingSuccess = "Бронирование успешно!";
            session()->forget('booking'); // Очистка сессии
        } else {
            $this->bookingSuccess = "Ошибка при бронировании!";
        }
    }

    public function render()
    {
        return view('livewire.booking-form')->extends('layouts.master');
    }
}

