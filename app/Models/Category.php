<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use Translatable;

    protected $fillable = [
        'title',
        'title_en',
        'hotel_id',
        'room_id',
        'food_id',
        'price',
        'price2',
        'rule_id',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

}
