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
                $donateur = Auth::user()->donateur;
    
        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun donateur lié à cet utilisateur.'
            ], 404);
        }
// dd(Auth::user());
            $campagnes = Participation::with('campagne')
                ->where('donateur_id', $donateur->id)
                ->get()
                ->pluck('campagne')
                ->unique('id')
                ->values();
// dd($campagnes);
            return response()->json([
                'status' => true,
                'message' => 'Campagnes récupérées avec succès',
                'data' => $campagnes,
            ], 200);
        }


       /**
     * Vérifier l'éligibilité du donateur à une campagne.
     */
public function verifierEligibilite($donateurId)
{
    $donateur = Donateur::findOrFail($donateurId);

    $problemes = [];

    // Vérifier l'âge
    $age = Carbon::parse($donateur->date_de_naissance)->age;
    if ($age < 18 || $age > 60) {
        $problemes[] = "L'âge doit être entre 18 et 60 ans.";
    }

    // Vérifier le poids
    if ($donateur->poids < 50) {
        $problemes[] = "Le poids doit être supérieur ou égal à 50 kg.";
    }

    // Vérifier la date du dernier don
    $dernierDon = Carbon::parse($donateur->date_dernier_don);
    $maintenant = Carbon::now();
    $diff = $dernierDon->diffInDays($maintenant);

    if ($donateur->sexe === 'homme' && $diff < 90) {
        $problemes[] = "Il faut attendre au moins 90 jours entre deux dons.";
    }

    if ($donateur->sexe === 'femme' && $dernierDon->diffInMonths($maintenant) < 3) {
        $problemes[] = "Il faut attendre au moins 3 mois entre deux dons.";
    }

    return response()->json([
        'est_eligible' => empty($problemes),
        'problemes' => $problemes,
    ]);
}

    public function inscriptionCampagne($campagneId)
    {
        $donateur = Auth::user()->donateur;
    
        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun donateur lié à cet utilisateur.'
            ], 404);
        }
    
        $campagne = Campagne::find($campagneId);
    
        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => "Campagne non trouvée."
            ], 404);
        }
    
        // ✅ Utilisation correcte de $donateur
        $already = Participation::where('donateur_id', $donateur->id)
            ->where('campagne_id', $campagneId)
            ->exists();
    
        if ($already) {
            return response()->json([
                'status' => false,
                'message' => 'Vous êtes déjà inscrit à cette campagne.'
            ], 409);
        }
    
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
    


 public function mettreAJourInformations(Request $request, $id)
{
    $donateur = Donateur::findOrFail($id);

    $request->validate([
        'poids' => 'required|numeric|min:30|max:200',
        'sexe' => 'required|in:M,F',
        // 'antecedent_medicament' => 'required|in:Aucun,Maladie chronique,hépathite,anémier,autre',
        'date_dernier_don' => 'nullable|date',
    ]);

    $donateur->update([
        'poids' => $request->poids,
        'sexe' => $request->sexe,
        // 'antecedent_medicament' => $request->antecedent_medicament,
        'date_dernier_don' => $request->date_dernier_don,
    ]);

    return response()->json([
        'message' => 'Informations mises à jour avec succès.',
        'donateur' => $donateur
    ]);
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