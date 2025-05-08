<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Campagne;
use Carbon\Carbon;
use App\Models\Donateur;
use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipationController extends Controller
{
   /**
     * Récupérer toutes les campagnes auxquelles le donateur connecté est inscrit.
     */
    public function historiquecampagnes()
    {
        $donateur = Auth::user();

        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Donateur non authentifié',
            ], 401);
        }

        $campagnes = Participation::with('campagne')
            ->where('donateur_id', $donateur->id)
            ->get()
            ->pluck('campagne')
            ->unique('id')
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Campagnes récupérées avec succès',
            'data' => $campagnes,
        ], 200);
    }

    /**
     * Récupérer tous les donateurs inscrits à une campagne spécifique.
     */
    // public function donateursParCampagne($campagneId)
    // {
    //     $organisateur = Auth::user();

    //     if (!$organisateur) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Organisateur non authentifié',
    //         ], 401);
    //     }
    //     $participations = Participation::with('donateur')
    //         ->where('campagne_id', $campagneId)
    //         ->get()
    //         ->pluck('donateur')
    //         ->unique('id')
    //         ->values();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Liste des donateurs inscrits à la campagne',
    //         'data' => $participations,
    //     ], 200);
    // }

    public function inscriptionCampagne( $campagneId)
{
    $donateur = Auth::user()->donateur;
    // dd($donateur);
    if (!$donateur) {
        return response()->json([
            'status' => false,
            'message' => 'Aucun donateur lié à cet utilisateur.'
        ], 404);
    }

    // Vérifier que la campagne existe
    $campagne = Campagne::find($campagneId);

    if (!$campagne) {
        return response()->json([
            'status' => false,
            'message' => "Campagne non trouvée."
        ], 404);
    }

    // === Vérification de l’éligibilité ===
    if ($donateur->poids < 50) {
        return response()->json([
            'status' => false,
            'message' => "Le poids minimum requis pour un don est de 50 kg."
        ], 403);
    }

    if ($donateur->date_dernier_don) {
        $prochain_don_possible = Carbon::parse($donateur->date_dernier_don)->addMonths(3);
        if (now()->lt($prochain_don_possible)) {
            return response()->json([
                'status' => false,
                'message' => "Vous ne pouvez pas donner à nouveau avant le " . $prochain_don_possible->format('d/m/Y') . "."
            ], 403);
        }
        
    }
// dd ($donateur->id);
    // === Création de la participation ===
    $participation = Participation::create([
        'donateur_id' => $donateur->id,
        'campagne_id' => $campagne->id,
        'statut' => 'en attente',
    ]);

    return response()->json([
        'status' => true,
        'message' => "Inscription réussie à la campagne.",
        'data' => $participation,
    ], 201);
}

    
    // récupérer les donateurs inscrits à une campagne spécifique
    public function donateursDeMaCampagne($campagneId)
{
    $user = Auth::user();

    // Vérifier que l'utilisateur est un organisateur
    if (!$user->hasRole('Organisateur')) {
        return response()->json([
            'status' => false,
            'message' => 'Seuls les organisateurs peuvent accéder aux donateurs de leurs campagnes.'
        ], 403);
    }

    $organisateur = $user->organisateur;

    if (!$organisateur) {
        return response()->json([
            'status' => false,
            'message' => 'Aucun organisateur lié à cet utilisateur.'
        ], 404);
    }

    // Vérifier que la campagne appartient bien à l'organisateur connecté
    $campagne = \App\Models\Campagne::where('id', $campagneId)
        ->where('organisateur_id', $organisateur->id)
        ->first();

    if (!$campagne) {
        return response()->json([
            'status' => false,
            'message' => 'Campagne non trouvée ou non autorisée.'
        ], 404);
    }

    // Récupérer les donateurs inscrits à cette campagne
    $donateurs = Participation::with('donateur')
        ->where('campagne_id', $campagneId)
        ->get()
        ->pluck('donateur')
        ->unique('id')
        ->values();

    return response()->json([
        'status' => true,
        'message' => 'Liste des donateurs inscrits à cette campagne.',
        'data' => $donateurs
    ], 200);
}

}