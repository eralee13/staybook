<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
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
        'checkin',
        'checkout',
        'phone',
        'email',
        'count',
        'type',
        'city',
        'address',
        'address_en',
        'city',
        'lng',
        'lat',
        'early_in',
        'late_out',
        'rating',
        'top',
        'user_id',
        'exely_id',
        'tourmind_id',
        'status'
    ];

    // protected $guarded = [
    //     'description',
    //     'address',
    //     'checkin',
    //     'checkout',
    //     'early_in',
    //     'late_out',
    //     'top',
    //     'user_id',
    //     'exely_id'
    // ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function categoryRooms()
    {
        return $this->hasMany(CategoryRoom::class, 'tourmind_id', 'tourmind_id');
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
}
