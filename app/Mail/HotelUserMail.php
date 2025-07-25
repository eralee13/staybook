<?php

namespace App\Mail;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HotelUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public Hotel $hotel;

    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    public function build()
    {
        return $this->subject('🏨 Спасибо за добавление отеля')
            ->view('mail.hotel_user');
    }
}