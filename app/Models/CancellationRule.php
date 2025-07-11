<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationRule extends Model
{
    use HasFactory, Translatable;

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
