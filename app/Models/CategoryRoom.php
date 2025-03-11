<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryRoom extends Model
{
    use HasFactory;
    use Translatable;

    protected $table = 'categoryRooms';

    protected $fillable = [
        'title',
        'title_en',
        'code'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
