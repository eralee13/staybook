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
        'address',
        'address_en',
        'city',
        'lng',
        'lat',
        'early_in',
        'early_out',
        'rating',
        'top',
        'user_id',
        'status'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'id', 'category_id');
    }

//    public function policy(){
//        return $this->hasOne(Policy::class);
//    }

    public function service()
    {
        return $this->hasOne(Service::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    public function food()
    {
        return $this->hasMany(Food::class);
    }

    public function rule()
    {
        return $this->hasMany(Rule::class);
    }

    public function child()
    {
        return $this->hasMany(Child::class);
    }


}
