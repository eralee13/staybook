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
        'title2',
        'titlec1',
        'titlec2',
        'titlec3',
        'phone',
        'email',
        'count',
        'countc',
        'sum',
        'status',
        'start_d',
        'end_d',
        'age1',
        'age2',
        'age3',
        'comment',
        'quote',
        'price',
        'tag'
    ];

    public function rooms(){
        return $this->hasMany(Room::class, 'id', 'room_id');
    }

    public function showStartDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->start_d)->format('d/m/Y');
    }

    public function showEndDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->end_d)->format('d/m/Y');
    }
}
