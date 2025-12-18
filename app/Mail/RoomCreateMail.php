<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoomCreateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public int $roomId) {}

    public function build()
    {
        $room = \App\Models\Room::with('hotel')->findOrFail($this->roomId);

        return $this->subject('Room created')
            ->view('mail.room_create', compact('room'));
    }

}