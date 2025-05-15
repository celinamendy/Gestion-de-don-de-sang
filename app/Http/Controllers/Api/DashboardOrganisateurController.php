<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campagne;
use App\Models\Participation;
use App\Models\StructureTransfusionSanguin;
use App\Models\Organisateur;
use App\Models\Donateur;
use App\Models\BanqueSang;
use Carbon\Carbon;
use App\Models\DemandeRavitaillement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardOrganisateurController extends Controller
{
    /**
     * Retourne les statistiques générales du dashboard de l'organisateur connecté.
     */
 public function statistiquesGenerales()
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

    $organisateurId = $organisateur->id;

    return response()->json([
    'totalCampagnes' => Campagne::where('organisateur_id', $organisateurId)->count(),
    'campagnesActives' => Campagne::where('organisateur_id', $organisateurId)
        ->where('statut', 'active')
        ->count(),
    'campagnesAVenir' => Campagne::where('organisateur_id', $organisateurId)
        ->where('date_debut', '>', now())
        ->count(),
    'pochesCollectees' => Participation::whereHas('campagne', function ($query) use ($organisateurId) {
        $query->where('organisateur_id', $organisateurId);
    })->sum('quantite'),
    'pochesCollecteesCeMois' => Participation::whereHas('campagne', function ($query) use ($organisateurId) {
        $query->where('organisateur_id', $organisateurId);
    })
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->sum('quantite'),
    'nombreDonneurs' => Donateur::count(),
    'nouveauxDonneursCeMois' => Donateur::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count(),
]);

}



    /**
     * Liste des campagnes actives.
     */
    public function campagnesActives()
    {
        $organisateur = Auth::user()->organisateur;
        
        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }

        return Campagne::where('organisateur_id', $organisateur->id)
            ->where('statut', 'active')
            ->orderByDesc('date_debut')
            ->get();
    }

    /**
     * Liste des campagnes à venir.
     */
    public function campagnesAVenir()
    {
        $today = Carbon::today(); // YYYY-MM-DD
        $organisateur = Auth::user()->organisateur;
        
        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }

        $campagnes = Campagne::where('organisateur_id', $organisateur->id)
            ->where('date_debut', '>', $today)
            ->orderBy('date_debut')
            ->get();

        return response()->json($campagnes);
    }

    /**
     * Liste des campagnes passées.
     */
    public function campagnesPassees()
    {
        $organisateur = Auth::user()->organisateur;
        
        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }

        return Campagne::where('organisateur_id', $organisateur->id)
            ->where('date_fin', '<', now())
            ->orderByDesc('date_fin')
            ->get();
    }

    /**
     * Les demandes de l'organisateur connecté
     */
    public function demandesParOrganisateurConnecte()
    {
        $organisateur = auth()->user()->organisateur;

        if (!$organisateur) {
            return response()->json(['message' => 'Aucun organisateur trouvé pour cet utilisateur.'], 404);
        }

        $demandes = DemandeRavitaillement::with(['stsDemandeur', 'groupeSanguin'])
            ->whereHas('stsDemandeur', function ($query) use ($organisateur) {
                $query->where('user_id', $organisateur->user_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($demandes->isEmpty()) {
            return response()->json([]);
        }

        return response()->json($demandes);
    }

    /**
     * Nombre de campagnes créées par mois (pour les graphiques).
     */
    public function campagnesParMois()
    {
        $organisateur = Auth::user()->organisateur;
        
        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }
        
        $campagnes = Campagne::selectRaw('MONTH(date_debut) as mois, COUNT(*) as total')
            ->where('organisateur_id', $organisateur->id)
            ->where('date_debut', '>=', now()->startOfYear())
            ->groupBy('mois')
            ->get();
            
        // Facultatif : traduire les mois en français
        $campagnes->transform(function ($item) {
            $item->mois = Carbon::create()->month($item->mois)->locale('fr_FR')->isoFormat('MMMM');
            return $item;
        });

        return response()->json($campagnes);
    }

    public function donsParMois() {
    $organisateur = Auth::user()->organisateur;

    $dons = Participation::whereHas('campagne', function($query) use ($organisateur) {
        $query->where('organisateur_id', $organisateur->id);
    })
    ->selectRaw('MONTH(created_at) as mois, SUM(quantite) as total')
    ->groupBy('mois')
    ->get();

    $dons->transform(function ($item) {
        $item->mois = Carbon::create()->month($item->mois)->locale('fr_FR')->isoFormat('MMMM');
        return $item;
    });

    return response()->json($dons);
}

    public function donsParRegion()
{
    $organisateur = Auth::user()->organisateur;
    $organisateurId = $organisateur->id;

   $donnees = DB::table('participations')
    ->join('campagnes', 'participations.campagne_id', '=', 'campagnes.id')
    ->join('structure_transfusion_sanguins', 'campagnes.structure_transfusion_sanguin_id', '=', 'structure_transfusion_sanguins.id')
    ->join('users as users_structure', 'structure_transfusion_sanguins.user_id', '=', 'users_structure.id')
    ->join('regions', 'users_structure.region_id', '=', 'regions.id')
    ->select('regions.libelle as region', DB::raw('SUM(participations.quantite) as total'))
    ->whereExists(function ($query) use ($organisateurId) {
        $query->select(DB::raw(1))
              ->from('campagnes')
              ->whereColumn('participations.campagne_id', 'campagnes.id')
              ->where('organisateur_id', $organisateurId);
    })
    ->groupBy('regions.libelle')
    ->get();

    return response()->json($donnees);

}

    /**
     * Répartition des donneurs par groupe sanguin.
     */
    public function donneursParGroupe()
    {
        $data = Donateur::with('groupe_sanguin') // Relation correctement nommée
            ->select('groupe_sanguin_id', DB::raw('count(*) as total'))
            ->groupBy('groupe_sanguin_id')
            ->get();
    
        return response()->json($data);
    }
    
    public function tauxParticipation()
    {
        $organisateur = Auth::user()->organisateur;
        if (!$organisateur) return response()->json(['message' => 'Organisateur non trouvé'], 404);

        $campagnes = Campagne::where('organisateur_id', $organisateur->id)->get();
        $data = [];

        foreach ($campagnes as $campagne) {
            $totalParticipants = Participation::where('campagne_id', $campagne->id)->count();
            $data[] = [
                'campagne' => $campagne->theme,
                'participants' => $totalParticipants,
            ];
        }

        return response()->json($data);
    }


    public function repartitionStatutsCampagnes()
    {
        $organisateur = Auth::user()->organisateur;
        
        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }

        $data = Campagne::where('organisateur_id', $organisateur->id)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();

        return response()->json($data);
    }
}