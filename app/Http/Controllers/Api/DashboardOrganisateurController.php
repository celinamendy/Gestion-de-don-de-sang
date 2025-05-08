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

    // ✅ Définir l'ID de l'organisateur ici
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
        'nombreDonneurs' => Donateur::count(),
    ]);
}


    /**
     * Liste des campagnes actives.
     */
    public function campagnesActives()
    {
        return Campagne::where('organisateur_id', Auth::id())
            ->where('statut', 'active')
            ->orderByDesc('date_debut')
            ->get();
    }

    /**
     * Liste des campagnes à venir.
     */
    // public function campagnesAvenir()
    // {
    //     return Campagne::where('organisateur_id', Auth::id())
    //         ->where('date_debut', '>', now())
    //         ->orderBy('date_debut')
    //         ->get();
    // }

    public function campagnesAVenir()
{
    $today = Carbon::today(); // YYYY-MM-DD

    $campagnes = Campagne::where('date_debut', '>', $today)->get();

    return response()->json($campagnes);
}

    /**
     * Liste des campagnes passées.
     */
    public function campagnesPassees()
    {
        return Campagne::where('organisateur_id', Auth::id())
            ->where('date_fin', '<', now())
            ->orderByDesc('date_fin')
            ->get();
    }

    /**
     * Les 5 dernières demandes urgentes.
     */
    //     public function demandesUrgentes()
    // {
    //     return DemandeRavitaillement::where('statut', 'urgence')
    //         ->orderByDesc('created_at')
    //         ->take(5)
    //         ->get();
    // }
    public function demandesUrgentes()
    {
        // Récupère les 5 dernières demandes urgentes
        return DemandeRavitaillement::where('statut', 'urgence')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
    }
    
    
    


    /**
     * Nombre de campagnes créées par mois (pour les graphiques).
     */
    public function campagnesParMois()
{
    $campagnes = Campagne::selectRaw('MONTH(date_debut) as mois, COUNT(*) as total')
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
}
