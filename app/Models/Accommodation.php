<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $table = 'accommodations';

    use Translatable;

    protected $fillable = [
        'hotel_id',
        'room_id',
        'without_place',
        'price_without',
        'extra_place',
        'price_extra',
        'age_to',
        'age_from',
        'without_place2',
        'price_without2',
        'extra_place2',
        'price_extra2',
        'age_to2',
        'age_from2',
        'without_place3',
        'price_without3',
        'extra_place3',
        'price_extra3',
        'age_to3',
        'age_from3',
        'status'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function category()
    {
        return $this->belongsTo(Rate::class);
    }

}
