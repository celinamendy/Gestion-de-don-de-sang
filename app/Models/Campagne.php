<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campagne extends Model
{
    protected $guarded = [];

    public function structureTransfusionSanguin()
    {
        return $this->belongsTo(StructureTransfusionSanguin::class);
    }
    

    public function organisateur()
    {
        return $this->belongsTo(Organisateur::class);
    }

    public function participations()
    {
        return $this->hasMany(Participation::class);
    }

    public function donateurs()
    {
        return $this->belongsToMany(Donateur::class, 'participations');
    }
}
