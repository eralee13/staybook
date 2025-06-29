<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['room_id', 'hotel_id', 'image', 'exely_id', 'caption', 'category'];

    public function rooms()
    {
        return $this->belongsToMany(Room::class);
    }
}
