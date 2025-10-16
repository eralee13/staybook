<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    protected $fillable = ['name','prefix','token_hash','scopes','partner_id','expires_at','last_used_at'];
    protected $casts = ['scopes' => 'array', 'expires_at'=>'datetime', 'last_used_at'=>'datetime'];
}
