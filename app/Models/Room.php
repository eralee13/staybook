<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Room extends Model
{
    use Translatable;
    use SoftDeletes;
    use QueryCacheable;
    protected $cacheFor = 180;

    protected $fillable = [
        'code',
        'title',
        'title_en',
        'description',
        'description_en',
        'hotel_id',
        'category_id',
        'count',
        'bed',
        'area',
        'services',
        'price',
        'price2',
        'image',
        'user_id',
        'exely_id',
        'status',
        'type_code'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryRoom::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

//    public function rooms()
//    {
//        return $this->hasMany(Room::class);
//    }

    public function images()
    {
        return $this->belongsToMany(Image::class, 'images');
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

}
