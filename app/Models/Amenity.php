<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Amenity extends Model
{
    protected $fillable = ['title', 'services', 'hotel_id'];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return HasOne
     */
    public function hotel(){
        return $this->hasOne(Hotel::class);
    }
}
