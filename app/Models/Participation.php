<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    protected $guarded = [];

    public function donateur()
    {
        return $this->belongsTo(Donateur::class);
    }
    
    public function campagne()
    {
        return $this->belongsTo(Campagne::class);
    }
    
}
