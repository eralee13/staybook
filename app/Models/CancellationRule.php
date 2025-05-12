<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'is_refundable', 'free_cancellation_days', 'penalty_type', 'penalty_amount', 'description', 'hotel_id'
    ];

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function bookings()
    {
        return $this->hasMany(Book::class);
    }

}
