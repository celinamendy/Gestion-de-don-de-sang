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

    if (!$user->hasAnyRole(['Organisateur', 'Structure_transfusion_sanguin'])) {
        return response()->json([
            'message' => 'Seuls les organisateurs ou les structures peuvent créer des campagnes.'
        ], 403);
    }

    $rules = [
         'theme' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lieu' => 'required|string|max:255',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'Heure_debut' => 'required|date_format:H:i',
            'Heure_fin' => 'required|date_format:H:i|after:Heure_debut',
            // 'participant' => 'required|integer|min:1',
            'statut' => 'required|string',    
    ];

    if ($user->hasRole('Organisateur')) {
        $rules['structure_transfusion_sanguin_id'] = 'required|exists:structure_transfusion_sanguins,id';
    }

    $validated = $request->validate($rules);

    // Validation croisée date + heure
    $debut = Carbon::parse($validated['date_debut'] . ' ' . $validated['Heure_debut']);
    $fin = Carbon::parse($validated['date_fin'] . ' ' . $validated['Heure_fin']);
    if ($fin->lessThanOrEqualTo($debut)) {
        return response()->json([
            'message' => 'La date et l\'heure de fin doivent être postérieures à celles de début.'
        ], 422);
    }

    $campagne = new Campagne($validated);

    if ($user->hasRole('Organisateur')) {
        $organisateur = $user->organisateur;
        if (!$organisateur) {
            return response()->json([
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }
        $campagne->organisateur_id = $organisateur->id;

    } elseif ($user->hasRole('Structure_transfusion_sanguin')) {
        $structure = $user->structure;
        if (!$structure) {
            return response()->json([
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }
        $campagne->structure_transfusion_sanguin_id = $structure->id;
    }

    try {
        $campagne->save();
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la création de la campagne.',
            'error' => $e->getMessage()
        ], 500);
    }

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
  $user = Auth::user();
    \Log::info('User connecté', ['id' => $user->id, 'email' => $user->email]);
    \Log::info('Rôles', ['roles' => $user->getRoleNames()]);
    \Log::info('Structure liée', ['structure' => $user->structureTransfusionSanguin]);

    $isOrganisateur = $user->hasRole('Organisateur') && $user->organisateur;
$isStructure = $user->hasRole('Structure_transfusion_sanguin') && $user->structure;

    \Log::info('isStructure?', ['val' => $isStructure]);

    if (!$isOrganisateur && !$isStructure) {
        return response()->json([
            'status' => false,
            'message' => 'Seuls les organisateurs ou structures peuvent modifier des campagnes.'
        ], 403);
    }

    $campagne = Campagne::find($id);

    if (!$campagne) {
        return response()->json([
            'status' => false,
            'message' => 'Campagne introuvable.'
        ], 404);
    }

    // Vérifie si la campagne appartient à l'utilisateur connecté
    if ($isOrganisateur && $campagne->organisateur_id !== $user->organisateur->id) {
        return response()->json([
            'status' => false,
            'message' => 'Vous n\'êtes pas autorisé à modifier cette campagne.'
        ], 403);
    }

$structure = $user->structure;

if ($isStructure && $structure && $campagne->structure_transfusion_sanguin_id !== $structure->id) {
        return response()->json([
            'status' => false,
            'message' => 'Vous n\'êtes pas autorisé à modifier cette campagne.'
        ], 403);
    }

    // Validation des données
    $validated = $request->validate([
        'theme' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'lieu' => 'nullable|string|max:255',
        'date_debut' => 'nullable|date',
        'date_fin' => 'nullable|date',
        'Heure_debut' => 'nullable|date_format:H:i',
        'Heure_fin' => 'nullable|date_format:H:i',
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
    $user = Auth::user();

    // Vérifie les rôles
    if (!$user->hasAnyRole(['Organisateur', 'Structure_transfusion_sanguin'])) {
        return response()->json([
            'status' => false,
            'message' => 'Seuls les organisateurs ou structures peuvent supprimer des campagnes.'
        ], 403);
    }

    $campagne = Campagne::find($id);

    if (!$campagne) {
        return response()->json([
            'status' => false,
            'message' => 'Campagne non trouvée.'
        ], 404);
    }

    // Vérifie si c’est une structure
    if ($user->hasRole('Structure_transfusion_sanguin')) {
        $structure = $user->structure;

        if (!$structure) {
            return response()->json([
                'status' => false,
                'message' => 'Aucune structure liée à cet utilisateur.'
            ], 404);
        }

        if ($campagne->structure_transfusion_sanguin_id !== $structure->id) {
            return response()->json([
                'status' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette campagne.'
            ], 403);
        }
    }

    // Vérifie si c’est un organisateur
    if ($user->hasRole('Organisateur')) {
        $organisateur = $user->organisateur;

        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur lié à cet utilisateur.'
            ], 404);
        }

        if ($campagne->organisateur_id !== $organisateur->id) {
            return response()->json([
                'status' => false,
                'message' => 'Vous n\'êtes pas autorisé à supprimer cette campagne.'
            ], 403);
        }
    }

    try {
        $campagne->delete();

        return response()->json([
            'status' => true,
            'message' => 'Campagne supprimée avec succès.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Erreur lors de la suppression.',
            'error' => $e->getMessage()
        ], 500);
    }
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

    $donateurs = $campagne->participations->pluck('donateur')->filter();

    return response()->json([
        'status' => true,
        'message' => 'Liste des donateurs récupérée avec succès.',
        'data' => $donateurs
    ], 200);
}

// public function valider($id)
// {
//     $campagne = Campagne::find($id);

//     if (!$campagne) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Campagne non trouvée.'
//         ], 404);
//     }

//     if ($campagne->statut === 'validée') {
//         return response()->json([
//             'status' => false,
//             'message' => 'Cette campagne est déjà validée.'
//         ], 400);
//     }

//     // Valider la campagne
//     $campagne->statut = 'validée';
//     $campagne->save();

//     return response()->json([
//         'status' => true,
//         'message' => 'Campagne validée avec succès.',
//         'data' => $campagne
//     ], 200);
// }


public function valider($id)
{
    $campagne = Campagne::find($id);

    if (!$campagne) {
        return response()->json([
            'status' => false,
            'message' => 'Campagne non trouvée.'
        ], 404);
    }

    if ($campagne->statut === 'validée') {
        return response()->json([
            'status' => false,
            'message' => 'Cette campagne est déjà validée.'
        ], 400);
    }

    // Valider la campagne
    $campagne->statut = 'validée';
    $campagne->save();

    // 🔔 Notifier l'organisateur
    if ($campagne->organisateur_id) {
        $organisateur = \App\Models\User::find($campagne->organisateur_id);

        if ($organisateur) {
            $structure = auth()->user(); // Structure connectée

            $message = "Votre campagne '{$campagne->theme}' a été validée par la structure '{$structure->name}'.";

            \App\Models\Notification::create([
                'user_id' => $organisateur->id,
                'message' => $message,
                'type' => 'validation_campagne',
                'statut' => 'non-lue',
                'created_at' => now(),
            ]);
        }
    }

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


