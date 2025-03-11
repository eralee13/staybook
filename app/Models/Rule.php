<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $table = 'rules';

    use Translatable;

    protected $fillable = [
        'hotel_id',
        'title',
        'title_en',
        'size',
        'percent_day',
        'percent_book',
        'date_day',
        'date_book'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function category()
    {
        return $this->belongsTo(Rate::class);
    }

}
