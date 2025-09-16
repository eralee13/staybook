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
        return $this->hasMany(Book::class, 'rate_id');
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

}
