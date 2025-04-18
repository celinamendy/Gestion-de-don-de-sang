<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeRavitaillement extends Model

{
    protected $guarded = [];

    public function stsDemandeur()
    {
        return $this->belongsTo(StructureTransfusionSanguine::class, 'sts_demandeur_id');
    }

    public function stsDestinataire()
    {
        return $this->belongsTo(StructureTransfusionSanguine::class, 'sts_destinataire_id');
    }

    public function groupeSanguin()
    {
        return $this->belongsTo(GroupeSanguin::class, 'groupe_sanguin_id');
    }
}