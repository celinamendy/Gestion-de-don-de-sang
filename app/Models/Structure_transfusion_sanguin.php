<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class Structure_transfusion_sanguin extends Model
{
    protected $guarded = [];
    use HasFactory, HasRoles;
// Structure appartient à une région
   
// Structure appartient à une campagne
    public function campagnes()
    {
        return $this->hasMany(Campagne::class);
    }
// Structure appartient à un banque de sang
    public function banque()
    {
        return $this->hasMany(BanqueDeSang::class);
    }
// Structure appartient à un utilisateur (Admin ou autre rôle)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function demandes_ravitaillement()
    {
        return $this->hasMany(Demande_ravitaillement::class, 'sts_demandeur_id');
    }
}
