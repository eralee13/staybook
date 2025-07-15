<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfflineMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offline;

    public function __construct($offline)
    {
        $this->offline = $offline;
    }

    public function build()
    {
        return $this->markdown('mail.offline')->subject('New Offline Request ' . $this->offline->id);
    }
}