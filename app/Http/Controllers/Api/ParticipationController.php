<?php

namespace App\Http\Controllers;

use App\Models\Participation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipationController extends Controller
{
    /**
     * Afficher toutes les participations.
     */
    public function index()
    {
        $participations = Participation::with(['donateur', 'campagne'])->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des participations récupérée avec succès',
            'data' => $participations
        ], 200);
    }

    /**
     * Récupérer les participations d’un utilisateur authentifié.
     */
    public function getParticipationsByDonateurId($id)
    {
        try {
            $donateurId = Auth::id();

            if (!$donateurId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Donateur non authentifié',
                ], 401);
            }

            $participations = Participation::where('donateur_id', $id)->with('campagne')->get();

            if ($participations->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucune participation trouvée pour ce donateur',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Participations récupérées avec succès',
                'data' => $participations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la récupération des participations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle participation.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'donateur_id' => 'required|exists:donateurs,id',
                'campagne_id' => 'required|exists:campagnes,id',
                'statut' => 'required|in:en attente,acceptée,refusée',
                'date_participation' => 'required|date',
                'lieu_participation' => 'required|string|max:255',
            ]);

            $participation = Participation::create($validated);
            
            return response()->json([
                'status' => true,
                'message' => 'Participation enregistrée avec succès',
                'data' => $participation
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la création de la participation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une participation spécifique.
     */
    public function show($id)
    {
        $participation = Participation::with(['donateur', 'campagne'])->find($id);

        if (!$participation) {
            return response()->json([
                'status' => false,
                'message' => 'Participation non trouvée',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Participation trouvée',
            'data' => $participation
        ]);
    }

    /**
     * Mettre à jour une participation.
     */
    public function update(Request $request, $id)
    {
        $participation = Participation::find($id);

        if (!$participation) {
            return response()->json([
                'status' => false,
                'message' => 'Participation non trouvée',
            ], 404);
        }

        $request->validate([
            'statut' => 'sometimes|in:en attente,acceptée,refusée',
            'date_participation' => 'sometimes|date',
            'lieu_participation' => 'sometimes|string|max:255',
        ]);

        $participation->update($request->only([
            'statut',
            'date_participation',
            'lieu_participation',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Participation mise à jour avec succès',
            'data' => $participation
        ]);
    }

    /**
     * Valider une participation (changer le statut en "acceptée").
     */
    public function validerParticipation($id)
    {
        $participation = Participation::find($id);

        if (!$participation) {
            return response()->json([
                'status' => false,
                'message' => 'Participation introuvable',
            ], 404);
        }

        $participation->update(['statut' => 'acceptée']);

        return response()->json([
            'status' => true,
            'message' => 'Participation acceptée avec succès'
        ]);
    }

    /**
     * Supprimer une participation.
     */
    public function destroy($id)
    {
        $participation = Participation::find($id);

        if (!$participation) {
            return response()->json([
                'status' => false,
                'message' => 'Participation non trouvée',
            ], 404);
        }

        $participation->delete();

        return response()->json([
            'status' => true,
            'message' => 'Participation supprimée avec succès',
        ]);
    }
}
