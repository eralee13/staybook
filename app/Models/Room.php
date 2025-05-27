<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Room extends Model
{
    use Translatable;
    //use QueryCacheable;
    //protected $cacheFor = 0;

    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
        'tourmind_id',
        'exely_id',
        'hotel_id',
        'area',
        'amenities',
        'image'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];


    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function images()
    {
        return $this->belongsToMany(Image::class, 'images');
    }
}
