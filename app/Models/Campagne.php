<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\StructureTransfusionSanguin;
use App\Models\Participation;
use App\Models\Donateur;
use App\Models\Organisateur;

class Campagne extends Model
{
    protected $guarded = [];

    protected $appends = ['participant'];





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
      // Accessor pour "participant"
    protected function participant(): Attribute
    {
        return Attribute::get(fn () => $this->participations()->count());
    }
}
