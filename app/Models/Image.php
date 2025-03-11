<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['room_id', 'image', 'exely_id', 'hotel_id'];

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }
}
