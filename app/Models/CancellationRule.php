<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationRule extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

    public function bookings()
    {
        return $this->hasMany(Book::class);
    }

}
