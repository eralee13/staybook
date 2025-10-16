<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meal extends Model
{
    protected $table = 'meals';

    use Translatable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'code',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return BelongsTo
     */
    public function rate()
    {
        return $this->belongsTo(Rate::class);
    }

}
