<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rate extends Model
{
    use Translatable;

    protected $guarded = [];

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings()
    {
        return $this->hasMany(\App\Models\Book::class, 'rate_id')
            ->where('status', 'reserved'); // как у вас принято
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function cancellationRule()
    {
        return $this->belongsTo(CancellationRule::class);
    }

    public function latestAllotment()
    {
        return $this->hasOne(\App\Models\Book::class, 'rate_id')
            ->where('api_type', 'calendar_allotment')
            ->latestOfMany('id'); // берём самую позднюю по id
    }

    public function latestPrice()
    {
        return $this->hasOne(\App\Models\Book::class, 'rate_id')
            ->where('api_type', 'calendar_price')
            ->latestOfMany('id');
    }

}
