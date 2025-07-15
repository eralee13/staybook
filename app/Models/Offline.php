<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offline extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'childAges' => 'array',
    ];

    public function showStartDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->arrivalDate)->format('d.m.Y');
    }

    public function showEndDate()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->departureDate)->format('d.m.Y');
    }
}
