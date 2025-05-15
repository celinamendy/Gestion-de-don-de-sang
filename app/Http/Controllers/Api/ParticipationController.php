<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Campagne;
use Carbon\Carbon;
use App\Models\Donateur;
use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    // Chemin complet vers le fichier JSON
    $cheminFichierEligibilite = storage_path('app/eligibilite.json');

    // Charger les règles d'éligibilité depuis le fichier JSON
    try {
        // Vérifier si le fichier existe
        if (!file_exists($cheminFichierEligibilite)) {
            throw new \Exception('Le fichier de règles d\'éligibilité n\'existe pas.');
        }

        // Lire le contenu du fichier
        $contenuJson = file_get_contents($cheminFichierEligibilite);
        
        // Décoder le JSON
        $regles = json_decode($contenuJson, true);
        
        // Vérifier si le décodage a réussi
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Erreur de parsing du fichier JSON : ' . json_last_error_msg());
        }

        // Vérifier si les règles sont vides
        if (empty($regles)) {
            throw new \Exception('Les règles d\'éligibilité sont vides.');
        }
    } catch (\Exception $e) {
        // Log de l'erreur
        \Log::error('Erreur de chargement des règles d\'éligibilité : ' . $e->getMessage());

        return [
            'est_eligible' => false,
            'problemes' => ['Erreur système dans la vérification d\'éligibilité.']
        ];
    }

    // Normaliser le sexe
    $sexe = strtolower($donateur->sexe);
    if ($sexe === 'h') {
        $sexe = 'homme';
    } elseif ($sexe === 'f') {
        $sexe = 'femme';
    }

    // Vérification du sexe
    if (!isset($regles[$sexe])) {
        return [
            'est_eligible' => false,
            'problemes' => ["Sexe non reconnu dans les règles d'éligibilité. Vous devez être du sexe 'femme' ou 'homme'."]
        ];
    }

    $regleSexe = $regles[$sexe];

    // Vérification de l'âge
    $age = Carbon::parse($donateur->date_naissance)->age;
    // dd($donateur->date_de_naissance);
    if (!$donateur->date_naissance) {
    $problemes[] = "Date de naissance manquante. Veuillez compléter votre profil.";
} else {
    $age = Carbon::parse($donateur->date_naissance)->age;
    if ($age < $regleSexe['age']['min'] || $age > $regleSexe['age']['max']) {
        $problemes[] = "Votre âge doit être compris entre {$regleSexe['age']['min']} et {$regleSexe['age']['max']} ans pour être éligible à cette campagne.";
    }
}


    // Vérification du poids
    if ($donateur->poids < $regleSexe['poids_min']) {
        $problemes[] = "Le poids doit être supérieur ou égal à {$regleSexe['poids_min']} kg pour que vous soyez reconnu(e) comme éligible à cette campagne.";
    }

    // Vérification de l'intervalle depuis le dernier don
    if ($donateur->date_dernier_don) {
        $dernierDon = Carbon::parse($donateur->date_dernier_don);
        $maintenant = Carbon::now();

        $jours = $dernierDon->diffInDays($maintenant);
        if ($jours < $regleSexe['intervalle_don_jours']) {
            $problemes[] = "Il faut attendre au moins {$regleSexe['intervalle_don_jours']} jours entre deux dons pour pouvoir faire un don.";
        }
    }

    return [
        'est_eligible' => empty($problemes),
        'problemes' => $problemes,
    ];
}

public function inscriptionCampagne(Request $request, $campagneId)
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

    // Vérification de l'éligibilité
    $eligibilite = $this->verifierEligibilite($donateur->id);
    
    if (!$eligibilite['est_eligible']) {
        return response()->json([
            'status' => false,
            'message' => 'Vous ne pouvais  pas vous inscrire à cette campagne car vous n\'etes pas éligible.',
            'problemes' => $eligibilite['problemes']
        ], 403);
    }

    // Vérification de l'inscription existante
    $already = Participation::where('donateur_id', $donateur->id)
        ->where('campagne_id', $campagneId)
        ->exists();

    if ($already) {
        return response()->json([
            'status' => false,
            'message' => 'Vous êtes déjà inscrit à cette campagne.'
        ], 409);
    }
    // Vérification de la date de fin de la campagne
   $date_fin = Carbon::parse($campagne->date_fin . ' ' . $campagne->Heure_fin); // Fusionne date et heure
    $dateActuelle = Carbon::now();

    if ($dateActuelle->greaterThan($date_fin)) {
        return response()->json([
            'status' => false,
            'message' => 'La campagne est déjà terminée.'
        ], 400);
    }


    // Création de la participation
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