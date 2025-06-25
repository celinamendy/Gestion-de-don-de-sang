<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campagne;
use App\Models\Donateur;
use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardDonateurController extends Controller
{
    /**
     * Affiche un message de bienvenue avec les infos de l'utilisateur connecté
     */
    public function dashboardDonateur()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'message' => 'Bienvenue Donateur !',
            'user' => $user,
        ]);
    }

    /**
     * Donne les infos principales du donateur pour le tableau de bord
     */
    public function index()
    {
        $donateur = Auth::user();

        // Nombre de dons (participations validées)
        $donsEffectues = Participation::where('donateur_id', $donateur->id)
            ->where('statut', 'validé')
            ->count();

        // Dernier don validé
        $dernierDon = Participation::where('donateur_id', $donateur->id)
            ->where('statut', 'validé')
            ->orderBy('created_at', 'desc')
            ->first();

        // Prochaine date de don autorisée
        $prochainDon = $dernierDon ? Carbon::parse($dernierDon->created_at)->addDays(60) : null;
        $joursRestants = $prochainDon ? Carbon::now()->diffInDays($prochainDon, false) : null;

        // Statut d'éligibilité
        $eligibilite = $joursRestants !== null && $joursRestants <= 0 ? 'Éligible' : 'Non éligible';

        // Système de badges simple (jusqu'à 3)
        $badges = $donsEffectues >= 3 ? 3 : $donsEffectues;

        // Historique des participations (les 8 dernières validées)
        $historique = Participation::with('campagne')
            ->where('donateur_id', $donateur->id)
            ->where('statut', 'validé')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Campagnes à venir
        $campagnes = Campagne::where('date_debut', '>=', Carbon::now())
            ->orderBy('date_debut', 'asc')
            ->limit(8)
            ->get();

        return response()->json([
            'donateur' => [
                'prenom' => $donateur->prenom,
                'nom' => $donateur->nom,
            ],
            'dons_effectues' => $donsEffectues,
            'prochain_don' => $joursRestants !== null ? ($joursRestants > 0 ? $joursRestants . ' jours' : 'Maintenant') : 'Jamais donné',
            'statut_eligibilite' => $eligibilite,
            'badges' => $badges,
            'campagnes' => $campagnes,
            'historique' => $historique,
        ]);
    }

    /**
     * Retourne la liste des campagnes à venir
     */
    public function campagnesAVenir(Request $request)
    {
        $campagnes = Campagne::where('date_debut', '>', now())
            ->orderBy('date_debut', 'asc')
            ->get();

        return response()->json($campagnes);
    }

    /**
     * Permet au donateur de s'inscrire à une campagne
     */
    public function inscriptionCampagne($id)
    {
        $donateur = Auth::user();

        $existe = Participation::where('donateur_id', $donateur->id)
            ->where('campagne_id', $id)
            ->exists();

        if ($existe) {
            return response()->json([
                'status' => false,
                'message' => 'Vous êtes déjà inscrit à cette campagne.',
            ], 409);
        }

        Participation::create([
            'donateur_id' => $donateur->id,
            'campagne_id' => $id,
            'statut' => 'en attente',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Inscription réussie à la campagne.',
        ]);
    }

    /**
     * Retourne l'historique complet des dons du donateur
     */
    public function historiqueDons()
    {
        $donateur = Auth::user();

        $historique = Participation::with('campagne')
            ->where('donateur_id', $donateur->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'historique' => $historique,
        ]);
    }

    /**
     * Vérifie si le donateur est éligible à faire un nouveau don
     */
    public function verifierEligibilite()
    {
        $donateur = Auth::user();

        $dernierDon = Participation::where('donateur_id', $donateur->id)
            ->where('statut', 'validé')
            ->orderBy('created_at', 'desc')
            ->first();

        $prochainDon = $dernierDon ? Carbon::parse($dernierDon->created_at)->addDays(60) : null;
        $joursRestants = $prochainDon ? Carbon::now()->diffInDays($prochainDon, false) : null;
        $eligible = $joursRestants !== null && $joursRestants <= 0;

        return response()->json([
            'status' => true,
            'eligible' => $eligible,
            'prochain_don_possible' => $joursRestants !== null ? ($joursRestants > 0 ? $joursRestants . ' jours' : 'Maintenant') : 'Jamais donné',
        ]);
    }

    /**
     * Lance un test d'éligibilité (placeholder pour une logique plus complexe)
     */
    public function lancerTestEligibilite()
    {
        $donateur = Auth::user();

        // Logique fictive ici, à personnaliser selon les critères
        $resultat = true;

        return response()->json([
            'status' => true,
            'message' => 'Test d\'éligibilité effectué avec succès.',
            'eligible' => $resultat,
        ]);
    }
}
