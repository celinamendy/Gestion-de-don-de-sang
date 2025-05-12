<?php

namespace App\Http\Controllers;

use App\Models\Campagne;
use App\Models\Demande;
use App\Models\BanqueDeSang;
use App\Models\Participation;
use App\Models\Donateur;
use App\Models\Organisateur;
use App\Models\StructureTransfusionSanguin;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampagneController extends Controller
{

    public function index()
    {
        $campagnes = Campagne::with('organisateur')->get(); // Utilisez 'organisateur' comme chaîne
        return response()->json([
            'status' => true,
            'message' => 'La liste des campagnes récupérée avec succès',
            'data' => $campagnes
        ], 200);
    }

    public function getAllcampagnes()
    {
        $campagnes  = Campagne::all();
        return response()->json([
            'status' => true,
            'message' => 'La liste des campagnes',
            'data' => $campagnes
        ]);
    }
    public function mescampagnes()
    {
        $user = Auth::user();
    
        if (!$user->hasRole('Organisateur')) {
            return response()->json([
                'status' => false,
                'message' => 'Seuls les organisateurs peuvent voir leurs campagnes.'
            ], 403);
        }
    
        $organisateur = $user->organisateur;
    
        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }
    
        $campagnes = Campagne::with('structureTransfusionSanguin')
            ->where('organisateur_id', $organisateur->id)
            ->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes récupérées avec succès.',
            'data' => $campagnes
        ], 200);
    }
    
//     public function getCampagnesByOrganisateurId($id)
// {
//     try {
//         // $campagnes = Campagne::with(['structure', 'organisateur'])
//         //     ->where('organisateur_id', $id)
//         //     ->get();
//             $campagnes = Campagne::with('structure')->where('organisateur_id', $id)->get();

//         return response()->json([
//             'success' => true,
//             'data' => $campagnes
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Erreur lors de la récupération des campagnes : ' . $e->getMessage()
//         ], 500);
//     }
// }

public function campagnesAVenir()
{
    $today = Carbon::today();
    $campagnes = Campagne::where('date_debut', '>', $today)
        ->orderBy('date_debut', 'asc')
        ->with('organisateur')
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Liste des campagnes à venir récupérée avec succès.',
        'data' => $campagnes
    ], 200);
}

    public function campagnesActives()
    {
        $today = Carbon::today();
        $campagnes = Campagne::where('date_debut', '>=', $today)
            ->where('statut', 'active')
            ->orderBy('date_debut', 'asc')
            ->with('organisateur')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes actives récupérée avec succès.',
            'data' => $campagnes
        ], 200);
    }
    public function campagnesPassées()
    {
        $today = Carbon::today();
        $campagnes = Campagne::where('date_fin', '<', $today)
            ->where('statut', '!=', 'validée')
            ->orderBy('date_fin', 'desc')
            ->with('organisateur')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes passées récupérée avec succès.',
            'data' => $campagnes
        ], 200);
    }
    public function campagnesValidees()
    {
        $campagnes = Campagne::where('statut', 'validée')
            ->orderBy('date_debut', 'asc')
            ->with('organisateur')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes validées récupérée avec succès.',
            'data' => $campagnes
        ], 200);
    }
    public function campagnesAnnulees()
    {
        $campagnes = Campagne::where('statut', 'annulée')
            ->orderBy('date_debut', 'asc')
            ->with('organisateur')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes annulées récupérée avec succès.',
            'data' => $campagnes
        ], 200);
    }



    public function getCampagnes($id)
{
    $organisateur = Organisateur::find($id);

    if (!$organisateur) {
        return response()->json(['error' => 'Organisateur not found'], 404);
    }

    $campagnes = $organisateur->campagnes; // Assurez-vous que la relation est bien définie dans le modèle Organisateur

    return response()->json($campagnes);
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
    

    public function update(Request $request, $id)
    {
        $campagne = Campagne::find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }
        $campagne->load('organisateur'); // ← AJOUT ICI

        $user = Auth::user();

        // Vérifie si l'utilisateur connecté est bien l'organisateur de cette campagne
        if (!$campagne->organisateur || $campagne->organisateur->user_id !== $user->id) {
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
        $campagne->load('organisateur');
        $user = Auth::user();

        if (!$campagne->organisateur || $campagne->organisateur->user_id !== $user->id) {
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

    public function validerParticipation($id)
    {
        $participation = Participation::findOrFail($id);
        $participation->statut = 'validée'; // ou true si c'est un booléen
        $participation->save();
    
        return response()->json(['message' => 'Participation validée avec succès.'], 200);
    }
    public function participants($id)
    {
        $campagne = Campagne::with('participations.donateur')->find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Liste des participants récupérée avec succès.',
            'data' => $campagne->participations
        ], 200);
    }
    public function valider($id)
    {
        $campagne = Campagne::find($id);

        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée.',
            ], 404);
        }

        $campagne->statut = 'validée'; // ou true si c'est un booléen
        $campagne->save();

        return response()->json([
            'status' => true,
            'message' => 'Campagne validée avec succès.',
            'data' => $campagne
        ], 200);
    }
    public function getCampagnesByStructureId($id)
    {
        $campagnes = Campagne::where('structure_transfusion_sanguin_id', $id)->with('organisateur')->get();
        if ($campagnes->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune campagne trouvée pour cette structure',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Liste des campagnes récupérée avec succès pour cette structure',
            'data' => $campagnes
        ], 200);    

}

}