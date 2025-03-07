<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['room_id', 'image'];

    public function rooms(){
        return $this->belongsToMany(Room::class);
    }
}
