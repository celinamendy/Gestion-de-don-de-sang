<?php

namespace App\Http\Controllers;

use App\Models\Campagne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampagneController extends Controller
{

public function index()
    {
        $campagnes  = Campagne::with(organisateur)->get();
        return response()->json([
            'status' => true,
            'message' => 'La liste des campagnes récupérée avec succes',
            'data' => $campagnes
        ],200);
    }
   



    public function store(Request $request)
    {
        $user = Auth::user();

        // Vérifie que l'utilisateur est un organisateur
        if (!$user->hasRole('Organisateur')) {
            return response()->json([
                'message' => 'Seuls les organisateurs peuvent créer des campagnes.'
            ], 403);
        }

        // Récupérer l'ID de l'organisateur lié à l'utilisateur
        $organisateur = $user->organisateur;

        if (!$organisateur) {
            return response()->json([
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }

        $validated = $request->validate([
            'theme' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lieu' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'Heure_debut' => 'required|date_format:H:i',
            'Heure_fin' => 'required|date_format:H:i|after:Heure_debut',
            'participant' => 'required|integer|min:1',
            'statut' => 'required|string',
            'structure_transfusion_sanguin_id' => 'required|exists:structure_transfusion_sanguins,id',
        ]);

        $campagne = new Campagne($validated);
        $campagne->organisateur_id = $organisateur->id;
        $campagne->save();

        return response()->json([
            'status' => true,
            'message' => 'Campagne créée avec succès.',
            'data' => $campagne
        ], 201);
    }

    public function show($id)
    {
        $campagne = Campagne::with('organisateur.user')->find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Détails de la campagne récupérés.',
            'data' => $campagne
        ], 200);
    }
    //  Récupérer les campagnes par l'id de l'organisateur   
    public function getCampagnesByOrganisateurId($id)
{
    $campagnes = Campagne::where('organisateur_id', $id)->with('organisateur')->get();

    if ($campagnes->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Aucune campagne trouvée pour cet organisateur',
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Liste des campagnes récupérée avec succès pour cet organisateur',
        'data' => $campagnes
    ], 200);
}


    public function update(Request $request, $id)
    {
        $campagne = Campagne::find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }

        $user = Auth::user();

        // Vérifie si l'utilisateur connecté est bien l'organisateur de cette campagne
        if ($campagne->organisateur->user_id !== $user->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à modifier cette campagne.'
            ], 403);
        }

        $validated = $request->validate([
            'theme' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'Heure_debut' => 'nullable|date_format:H:i',
            'Heure_fin' => 'nullable|date_format:H:i',
            'participant' => 'nullable|integer|min:1',
            'statut' => 'nullable|string',
            'structure_transfusion_sanguin_id' => 'nullable|exists:structure_transfusion_sanguins,id',
        ]);

        $campagne->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Campagne mise à jour avec succès.',
            'data' => $campagne
        ], 200);
    }

    public function destroy($id)
    {
        $campagne = Campagne::find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }

        $user = Auth::user();

        if ($campagne->organisateur->user_id !== $user->id) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette campagne.'
            ], 403);
        }

        $campagne->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campagne supprimée avec succès.',
        ], 200);
    }
}
