<?php

namespace App\Http\Controllers;

use App\Models\Campagne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StructureTransfusionSanguin;
use App\Models\Organisateur;
use App\Models\User;
use App\Models\Donateur;
use App\Models\Participation;
use App\Models\Banque_sang;
use App\Models\DemandeRavitaillement;
use App\Models\Notification;
use App\Models\Region;
class CampagneStructureController extends Controller
{
    /**
     * Affiche toutes les campagnes en incluant les informations de la structure transfusion sanguine.
     */
    public function index()
    {
        // Récupère toutes les campagnes avec les infos de la structure associée
        $campagnes = Campagne::with('structure_transfusion_sanguin')->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes liées à la structure.',
            'data' => $campagnes
        ], 200);
    }

    /**
     * Crée une nouvelle campagne pour une structure.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Vérifie que l'utilisateur a le rôle "Structure_transfusion_sanguin"
        if (!$user->hasRole('Structure_transfusion_sanguin')) {
            return response()->json([
                'message' => 'Seules les structures peuvent créer des campagnes.'
            ], 403);
        }

        // Récupère la structure liée à l'utilisateur connecté
        $structure = $user->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.',
            ], 404);
        }

        // Valide les données reçues
        $validated = $request->validate([
            'theme' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lieu' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:Heure_debut',
            'participant' => 'required|integer|min:1',
            'statut' => 'required|string',
            'organisateur_id' => 'required|exists:organisateurs,id',
        ]);

        // Crée une nouvelle campagne avec les données validées
        $campagne = new Campagne($validated);
        $campagne->structure_transfusion_sanguin_id = $structure->id;
        $campagne->save();

        return response()->json([
            'status' => true,
            'message' => 'Campagne créée avec succès.',
            'data' => $campagne
        ], 201);
    }

    /**
     * Affiche les détails d'une campagne donnée par son ID.
     */
    public function show($id)
    {
        // Recherche la campagne avec les données de la structure (et utilisateur de la structure)
        $campagne = Campagne::with('structure_transfusion_sanguin.user')->find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée ou non liée à votre structure.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Détails de la campagne récupérés.',
            'data' => $campagne
        ], 200);
    }
    /**
     * Récupère une structure transfusion sanguine par l'ID de l'organisateur.
     */
    public function getByOrganisateur($id)
{
    $structure = StructureTransfusionSanguin::where('organisateur_id', $id)->first();

    if (!$structure) {
        return response()->json(['message' => 'Structure non trouvée.'], 404);
    }

    return response()->json([
        'status' => true,
        'data' => $structure
    ]);
}


    /**
     * Récupère les campagnes d'une structure spécifique par son ID.
     */
    public function getCampagnesByStructureId($id)
    {
        $campagnes = Campagne::where('structure_transfusion_sanguin_id', $id)
            ->with('organisateur')
            ->get();

        if ($campagnes->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune campagne trouvée pour cette structure.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes pour cette structure.',
            'data' => $campagnes
        ], 200);
    }

    /**
     * Met à jour une campagne spécifique appartenant à la structure connectée.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $structure = $user->structure;

        // Vérifie que la campagne appartient bien à la structure connectée
        $campagne = Campagne::where('id', $id)
            ->where('structure_transfusion_sanguin_id', $structure->id)
            ->first();

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée ou non liée à votre structure.',
            ], 404);
        }

        // Valide les données de mise à jour
        $validated = $request->validate([
            'theme' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'participant' => 'nullable|integer|min:1',
            'statut' => 'nullable|string',
            'organisateur_id' => 'nullable|exists:organisateurs,id',
        ]);

        // Met à jour la campagne
        $campagne->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Campagne mise à jour avec succès.',
            'data' => $campagne
        ], 200);
    }

    /**
     * Supprime une campagne appartenant à la structure connectée.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $structure = $user->structure;

        // Vérifie que la campagne appartient bien à la structure connectée
        $campagne = Campagne::where('id', $id)
            ->where('structure_transfusion_sanguin_id', $structure->id)
            ->first();

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée ou non liée à votre structure.',
            ], 404);
        }

        // Supprime la campagne
        $campagne->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campagne supprimée avec succès.',
        ], 200);
    }
}
