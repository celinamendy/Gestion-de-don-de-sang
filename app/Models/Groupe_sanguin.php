<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe_sanguin extends Model
{
    protected $guarded = [];

    public function donateurs()
    {
        return $this->hasMany(Donateur::class);
    }

    public function banques()
    {
        return $this->hasMany(BanqueDeSang::class);
    }
}
