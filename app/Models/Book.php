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
        'title1',
        'title2',
        'title3',
        'title4',
        'title5',
        'title6',
        'title7',
        'title8',
        'child_name',
        'child_name2',
        'child_name3',
        'child_name4',
        'child_name5',
        'child_name6',
        'child_name7',
        'child_name8',
        'phone',
        'email',
        'comment',
        'adult',
        'child',
        'childages',
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
        'status',
        'user_id',
        'api_type',
        'agent_ref',
        'allotment',
        'room_count'
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

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function rate()
    {
        return $this->belongsTo(Rate::class);
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
