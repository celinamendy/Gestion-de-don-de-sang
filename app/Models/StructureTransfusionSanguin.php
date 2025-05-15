<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class StructureTransfusionSanguin extends Model
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
        return $this->hasMany(BanqueSang::class);
    }
// Structure appartient à un utilisateur (Admin ou autre rôle)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function organisateur()
    // {
    //     return $this->belongsTo(Organisateur::class);
    // }

    public function demandesRavitaillementEnvois()
    {
        return $this->hasMany(DemandeRavitaillement::class, 'sts_demandeur_id');
    }

    public function demandesRavitaillementRecus()
    {
        return $this->hasMany(DemandeRavitaillement::class, 'sts_destinataire_id');
    }
}
