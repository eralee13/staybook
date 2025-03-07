<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
    use HasFactory;
    use Translatable;

    protected $fillable = [
        'title',
        'title_en',
        'code'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
