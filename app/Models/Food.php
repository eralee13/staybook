<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    use Translatable;

    protected $fillable = [
        'title',
        'title_en',
        'price',
        'hotel_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
