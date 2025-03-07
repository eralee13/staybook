<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use Translatable;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'title_en',
        'description',
        'description_en',
        'image',
        'hotel_id',
        'count',
        'status',
        'bed',
        'area',
        'services',
        'price',
        'price2',
        'user_id',
        'cat_id'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
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

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

    public function cat()
    {
        return $this->belongsTo(Cat::class);
    }

}
