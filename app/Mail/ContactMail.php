<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->markdown('mail.contact')->subject(config('app.name') . 'Контакты');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Заявка с страницы Контакты',
        );
    }

}