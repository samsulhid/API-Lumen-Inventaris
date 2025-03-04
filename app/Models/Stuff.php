<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class stuff extends Model
{
    use  softDeletes; 
    protected $fillable = ["name", "category"];

    public function stuffStock()
    {
        return $this ->hasOne(stuffStock::class);
    }
    
    public function inboundStuff()
    {
        return $this ->hasMany(inboundStuff::class);
    }

    public function lendings()
    {
        return $this ->hasMany(lendings::class);
    }
}
