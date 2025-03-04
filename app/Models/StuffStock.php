<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class stuffStock extends Model
{
    use  softDeletes;
    protected $fillable = ['stuff_id', 'total_available', 'total_defec'];

    public function  stuff() 
    {
        return $this->belongsTo(stuff::class);
    }
}
