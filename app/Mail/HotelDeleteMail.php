<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HotelDeleteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hotel;

    public function __construct($hotel)
    {
        $this->hotel = $hotel;
    }

    public function build()
    {
        return $this->markdown('mail.hotel_delete')->subject('Property '. $this->hotel->title_en.' deleted');
    }

}