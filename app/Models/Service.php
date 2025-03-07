<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['title', 'services', 'hotel_id'];

    public function hotel(){
        return $this->hasOne(Hotel::class);
    }
}
