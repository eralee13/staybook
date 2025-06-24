<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rate extends Model
{
    use Translatable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'title_en',
        'hotel_id',
        'room_id',
        'meal_id',
        'price',
        'price2',
        'price3',
        'price4',
        'desc_en',
        'rate_code',
        'allotment',
        'currency',
        'total_price',
        'adult',
        'child',
        'bed_type',
        'children_allowed',
        'free_children_age',
        'child_extra_fee',
        'availability',
        'cancellation_rule_id',
        'open_time',
        'close_time',
    ];

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
        return $this->hasMany(Book::class);
    }

    /**
     * @return BelongsTo
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    /**
     * @return BelongsTo
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    /**
     * @return BelongsTo
     */
    public function cancellationRule()
    {
        return $this->belongsTo(CancellationRule::class);
    }

}
