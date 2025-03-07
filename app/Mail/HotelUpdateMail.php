<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HotelUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hotel;

    public function __construct($hotel)
    {
        $this->hotel = $hotel;
    }

    public function build()
    {
        return $this->markdown('mail.hotel_update')->subject('Property '. $this->hotel->title_en.' updated ' );
    }

}