<?php

namespace App\Models;

use App\Models\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use Translatable;
    protected $fillable = ['phone', 'email', 'phone2', 'address', 'address_en', 'whatsapp', 'telegram', 'instagram'];
}
