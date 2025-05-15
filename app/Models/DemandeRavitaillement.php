<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\StructureTransfusionSanguin;
use Illuminate\Database\Eloquent\Model;

class DemandeRavitaillement extends Model

{
    protected $guarded = [];

   public function stsDemandeur()
    {
        return $this->belongsTo(StructureTransfusionSanguin::class, 'sts_demandeur_id');
    }

    public function stsDestinataire()
    {
        return $this->belongsTo(StructureTransfusionSanguin::class, 'sts_destinataire_id');
    }

    public function groupeSanguin()
    {
        return $this->belongsTo(GroupeSanguin::class, 'groupe_sanguin_id');
    }

// App\Models\DemandeRavitaillement.php

    // public function structureTransfusionSanguin()
    // {
    //     return $this->belongsTo(StructureTransfusionSanguin::class, 'structure_transfusion_sanguin_id');
    // }


}