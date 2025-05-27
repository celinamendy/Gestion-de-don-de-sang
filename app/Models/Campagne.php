<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\StructureTransfusionSanguin;


class Campagne extends Model
{
    protected $guarded = [];

    // public function structureTransfusionSanguin()
    // {
    //     return $this->belongsTo(StructureTransfusionSanguin::class);
    // }
    public function structure_transfusion_sanguin()
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
