<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campagne;
use App\Models\Participation;
use App\Models\StructureTransfusionSanguin;
use App\Models\Donateur;
use App\Models\DemandeRavitaillement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StructureDashboardController extends Controller

 
{
//    


    public function campagnes()
{
    try {
        $campagnes = Campagne::with( 'structureTransfusionSanguin')->get();

        return response()->json($campagnes);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la récupération des campagnes',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function statistiquesGenerales()
    {
        $user = Auth::user();

        if (!$user->hasRole('Structure_transfusion_sanguin')) {
            return response()->json([
                'status' => false,
                'message' => 'Seules les structures peuvent accéder à ces données.'
            ], 403);
        }

        $structure = $user->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }

        $structureId = $structure->id;

        return response()->json([
            'totalCampagnes' => Campagne::where('structure_transfusion_sanguin_id', $structureId)->count(),
            'campagnesActives' => Campagne::where('structure_transfusion_sanguin_id', $structureId)
                ->where('statut', 'active')
                ->count(),
            'campagnesAVenir' => Campagne::where('structure_transfusion_sanguin_id', $structureId)
                ->where('date_debut', '>', now())
                ->count(),
            'pochesCollectees' => Participation::whereHas('campagne', function ($query) use ($structureId) {
                $query->where('structure_transfusion_sanguin_id', $structureId);
            })->sum('quantite'),
            'pochesCollecteesCeMois' => Participation::whereHas('campagne', function ($query) use ($structureId) {
                $query->where('structure_transfusion_sanguin_id', $structureId);
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

    public function campagnesActives()
    {

        $structure = Auth::user()->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }

        return Campagne::where('structure_transfusion_sanguin_id', $structure->id)
            ->where('statut', 'active')
            ->orderByDesc('date_debut')
            ->get();
    }

    public function campagnesAVenir()
    {
        $today = Carbon::today();
        $structure = Auth::user()->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }

        return Campagne::where('structure_transfusion_sanguin_id', $structure->id)
            ->where('date_debut', '>', $today)
            ->orderBy('date_debut')
            ->get();
    }

   public function CampagnesPassees()
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $structureId = $user->id;

        $campagnesPassees = Campagne::where('structure_transfusion_sanguin_id', $structureId)
            ->whereDate('date_fin', '<', now())
            ->get();

        return response()->json(['data' => $campagnesPassees], 200);

    } catch (\Exception $e) {
        // Log de l'erreur pour investigation
        Log::error('Erreur dans getCampagnesPassees: ' . $e->getMessage());

        // Réponse JSON d'erreur
        return response()->json([
            'message' => 'Une erreur est survenue lors de la récupération des campagnes passées.',
            'erreur' => $e->getMessage() // à retirer en prod si tu veux éviter d’exposer les détails
        ], 500);
    }
}

    public function campagnesParMois()
    {
        $structure = Auth::user()->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }

        $campagnes = Campagne::selectRaw('MONTH(date_debut) as mois, COUNT(*) as total')
            ->where('structure_transfusion_sanguin_id', $structure->id)
            ->where('date_debut', '>=', now()->startOfYear())
            ->groupBy('mois')
            ->get();

        $campagnes->transform(function ($item) {
            $item->mois = Carbon::create()->month($item->mois)->locale('fr_FR')->isoFormat('MMMM');
            return $item;
        });

        return response()->json($campagnes);
    }

    public function donsParMois()
    {
        $structure = Auth::user()->structure;

        $dons = Participation::whereHas('campagne', function ($query) use ($structure) {
            $query->where('structure_transfusion_sanguin_id', $structure->id);
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
        $structure = Auth::user()->structure;
        $regionId = $structure->user->region_id;

        $donnees = DB::table('participations')
            ->join('campagnes', 'participations.campagne_id', '=', 'campagnes.id')
            ->join('structure_transfusion_sanguins', 'campagnes.structure_transfusion_sanguin_id', '=', 'structure_transfusion_sanguins.id')
            ->join('users as users_structure', 'structure_transfusion_sanguins.user_id', '=', 'users_structure.id')
            ->join('regions', 'users_structure.region_id', '=', 'regions.id')
            ->select('regions.libelle as region', DB::raw('SUM(participations.quantite) as total'))
            ->where('regions.id', $regionId)
            ->groupBy('regions.libelle')
            ->get();

        return response()->json($donnees);
    }

    public function donneursParGroupe()
    {
        $data = Donateur::with('groupe_sanguin')
            ->select('groupe_sanguin_id', DB::raw('count(*) as total'))
            ->groupBy('groupe_sanguin_id')
            ->get();

        return response()->json($data);
    }

    public function tauxParticipation()
    {
        $structure = Auth::user()->structure;

        if (!$structure) return response()->json(['message' => 'Structure non trouvée'], 404);

        $campagnes = Campagne::where('structure_transfusion_sanguin_id', $structure->id)->get();
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
        $structure = Auth::user()->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }

        $data = Campagne::where('structure_transfusion_sanguin_id', $structure->id)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->get();

        return response()->json($data);
    }

    public function demandesParStructure()
{
    $structure = Auth::user(); // relation correcte à adapter

    if (!$structure) {
        return response()->json([
            'status' => false,
            'message' => 'Aucune structure associée à cet utilisateur.'
        ], 403);
    }

    $demandes = DemandeRavitaillement::where('sts_demandeur_id', $structure->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($demandes);
}

}
