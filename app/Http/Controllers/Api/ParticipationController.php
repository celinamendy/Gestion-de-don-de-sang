<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

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
    public function donateursParCampagne($campagneId)
    {
        $organisateur = Auth::user();

        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Organisateur non authentifié',
            ], 401);
        }
        $participations = Participation::with('donateur')
            ->where('campagne_id', $campagneId)
            ->get()
            ->pluck('donateur')
            ->unique('id')
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Liste des donateurs inscrits à la campagne',
            'data' => $participations,
        ], 200);
    }
}