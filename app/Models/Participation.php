<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participation extends Model
{
    protected $guarded = [];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function campagne()
    {
        return $this->belongsTo(Campagne::class);
    }
    
}
