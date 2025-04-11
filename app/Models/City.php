<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class City extends Model
{
    use HasFactory;
    use QueryCacheable;
    protected $cacheFor = 180;

    public $fillable = [
        'title',
        'code',
        'exely_id',
    ];

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }
}
