<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BanqueSang extends Model
{
    protected $guarded = [];
// Cette banque appartient à une structure de transfusion sanguine
    public function structure()
    {
        return $this->belongsTo(Structure_transfusion_sanguin::class);
    }
// Cette banque appartient à une groupe sanguin
    public function groupe_sanguin()
    {
        return $this->belongsTo(Groupe_sanguin::class);
    }
}
