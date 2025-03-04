<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\softDeletes;

class inboundStuff extends Model
{
    use  softDeletes;
    protected $fillable = ["stuff_id", "total", "date", "proff_file"];

    public function stuff()
    {
        return $this->belongsTo(stuff::class);
    }
}
