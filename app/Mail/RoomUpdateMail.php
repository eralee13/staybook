<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoomUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public int $roomId) {}

    public function build()
    {
        $room = \App\Models\Room::with('hotel')->findOrFail($this->roomId);

        return $this->subject('Room updated')
            ->view('mail.room_update', compact('room'));
    }
}