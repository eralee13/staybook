<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        //'code',
        'title',
        'type',
        'city',
        'address',
        'count',
        'status'
    ];
}
