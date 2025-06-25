<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participation;
use App\Models\Campagne;


class DashboardController extends Controller
{
    
    
    public function getCampagnesByStatut($statut)
    {
        $campagnes = Campagne::where('statut', $statut)->with('organisateur')->get();
        if ($campagnes->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune campagne trouvée avec ce statut',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes récupérée avec succès pour ce statut',
            'data' => $campagnes
        ], 200);    

    }
//     public function campagnesAVenir()
// {
//     $today = Carbon::today();
//     $campagnes = Campagne::where('date_debut', '>', $today)
//                           ->where('statut', 'active')
//                           ->orderBy('date_debut', 'asc')
//                           ->with('organisateur')
//                           ->get();

//     return response()->json([
//         'status' => true,
//         'message' => 'Liste des campagnes à venir récupérée avec succès.',
//         'data' => $campagnes
//     ], 200);
// }



public function demandesUrgentes()
{
    $demandes = Demande::where('statut', 'urgent')
                        ->orWhere('statut', 'critique')
                        ->orderBy('created_at', 'desc')
                        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Liste des demandes urgentes récupérée avec succès.',
        'data' => $demandes
    ], 200);
}

public function statistiques()
{
    $totalDonateurs = Donateur::count();
    $totalCampagnes = Campagne::count();
    $pochesCollectees = BanqueDeSang::sum('quantite_stockee'); // À adapter selon ton modèle BanqueDeSang
    $demandesUrgentes = Demande::where('statut', 'urgent')->count();

    return response()->json([
        'status' => true,
        'message' => 'Statistiques récupérées avec succès.',
        'data' => [
            'donateurs' => $totalDonateurs,
            'campagnes' => $totalCampagnes,
            'poches_collectees' => $pochesCollectees,
            'demandes_urgentes' => $demandesUrgentes,
        ]
    ], 200);
}
public function participations($id)
{
    // Vérifier si la campagne existe
    $campagne = Campagne::find($id);
    if (!$campagne) {
        return response()->json(['message' => 'Campagne non trouvée'], 404);
    }

    // Charger les participants (en supposant qu'il y a une relation)
    $participants = $campagne->donateurs; // Ou autre nom de relation

    return response()->json($participants, 200);
}
}
