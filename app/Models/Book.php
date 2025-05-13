<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'hotel_id',
        'room_id',
        'title',
        'phone',
        'email',
        'comment',
        'adult',
        'child',
        'childAges',
        'price',
        'sum',
        'arrivalDate',
        'departureDate',
        'book_token',
        'currency',
        'cancellations',
        'cancellation_id',
        'cancel_penalty',
        'rate_id',
        'tag',
        'status',
        'user_id',
        'api_type',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'tag',
        'status',
        'price',
        'title2',
        'count'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function showStartDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->arrivalDate)->format('d.m.Y');
    }

    public function showEndDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->departureDate)->format('d.m.Y');
    }
}
