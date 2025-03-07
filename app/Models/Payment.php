<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['payments', 'hotel_id'];

    public function hotel(){
        return $this->hasOne(Hotel::class);
    }
}
