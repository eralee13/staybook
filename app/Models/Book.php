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
        'title',
        'hotel_id',
        'room_id',
        'rate_id',
        'user_id',
        'phone',
        'email',
        'adult',
        'child',
        'childages',
        'child_name',
        'room_count',
        'price',
        'sum',
        'arrivalDate',
        'departureDate',
        'book_token',
        'status',
        'cancellation_id',
        'cancel_penalty',
        'cancel_date',
        'api_type',
        'checkin_request',
        'checkin_time',
        'checkout_request',
        'checkout_time',
        'source_sym',
        'cancel_price_source',
        'currency', // ← добавили, чтобы валюта сохранялась
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'tag',
        'status',
        // 'price', // ← НЕ скрываем цену
        'count',
    ];

    // Приведение типов: корректная сериализация и работа с датами/суммами
    protected $casts = [
        'price'        => 'decimal:2',
        'sum'          => 'decimal:2',
        'arrivalDate'  => 'date',     // храните Y-m-d? отлично, станет Carbon date
        'departureDate'=> 'date',
        'cancel_date'  => 'datetime',
        'checkin_time' => 'datetime',
        'checkout_time'=> 'datetime',
    ];

    public function hotel()   { return $this->belongsTo(Hotel::class); }
    public function room()    { return $this->belongsTo(Room::class); }
    public function rate()    { return $this->belongsTo(Rate::class); }

    public function showStartDate()
    {
        // благодаря casts это Carbon|null
        return optional($this->arrivalDate)->format('d.m.Y');
    }

    public function showEndDate()
    {
        return optional($this->departureDate)->format('d.m.Y');
    }

    public function scopeCalendarPrice($q)     { return $q->where('api_type','calendar_price'); }
    public function scopeCalendarAllotment($q) { return $q->where('api_type','calendar_allotment'); }


}
