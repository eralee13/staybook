<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationRule extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $fillable = [
        'title', 'is_refundable', 'free_cancellation_days', 'penalty_type', 'penalty_amount', 'description', 'hotel_id', 'end_date'
    ];

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

    public function bookings()
    {
        return $this->hasMany(Book::class);
    }

}
