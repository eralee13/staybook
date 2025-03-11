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
//        'titlec1',
//        'titlec2',
//        'titlec3',
        'phone',
        'email',
        'comment',
        'adult',
        'child',
        'price',
        'sum',
        'arrivalDate',
        'departureDate',
        'book_token',
//        'age1',
//        'age2',
//        'age3',
        'tag',
        'status',
        'user_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'tag',
        'status',
        'price'
    ];

    public function rooms(){
        return $this->hasMany(Room::class, 'id', 'room_id');
    }

    public function showStartDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->arrivalDate)->format('d/m/Y');
    }

    public function showEndDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->departureDate)->format('d/m/Y');
    }
}
