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
        'rule_id',
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
    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
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
    public function rule()
    {
        return $this->belongsTo(Rule::class);
    }

}
