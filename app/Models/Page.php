<?php

namespace App\Models;
use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Page extends Model
{
    use Translatable;
    use QueryCacheable;
    protected $cacheFor = 180;

    protected $fillable = ['title', 'title_en', 'description', 'description_en'];
}
