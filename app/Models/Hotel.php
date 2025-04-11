<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Hotel extends Model
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
        'image',
        'checkin',
        'checkout',
        'phone',
        'email',
        'count',
        'type',
        'city',
        'address',
        'address_en',
        'lng',
        'lat',
        'early_in',
        'late_out',
        'rating',
        'top',
        'user_id',
        'exely_id',
        'timezone',
        'status'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class, 'id', 'rate_id');
    }

//    public function policy(){
//        return $this->hasOne(Policy::class);
//    }

    public function amenity()
    {
        return $this->hasOne(Amenity::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function meals()
    {
        return $this->hasMany(Meal::class);
    }

    public function rules()
    {
        return $this->hasMany(Rule::class);
    }

    public function accommodations()
    {
        return $this->hasMany(Accommodation::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
