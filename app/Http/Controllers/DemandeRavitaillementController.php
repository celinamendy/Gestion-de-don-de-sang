<?php

namespace App\Http\Controllers;

use App\Models\DemandeRavitaillement;
use App\Models\Groupe_sanguin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DemandeRavitaillementController extends Controller
{
    
public function index()
{
        $user = Auth::user();

    // Vérifie que l'utilisateur a le rôle "Structure_transfusion_sanguin"
    if (!$user->hasRole('Structure_transfusion_sanguin')) {
        return response()->json([
            'message' => 'Seules les structures peuvent consulter leurs campagnes.'
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

    $demandes = DemandeRavitaillement::with(['stsDemandeur', 'stsDestinataire', 'groupeSanguin'])->get();

    return response()->json($demandes);
}

    // public function indexParOrganisateur()
    // {
    //     $user = auth()->user();

    //     if (!$user->organisateur_id) {
    //         return response()->json(['message' => 'Non autorisé'], 403);
    //     }

    //         $demandes = Demande::where('organisateur_id', $user->organisateur_id)->get();
    //     return response()->json($demandes);
    // }

    public function store(Request $request)
{
    $user = Auth::user();

    // Vérifie que l'utilisateur a le rôle approprié
    if (!$user->hasRole('Structure_transfusion_sanguin')) {
        return response()->json([
            'message' => 'Seules les structures peuvent effectuer des demandes de ravitaillement.'
        ], 403);
    }

    // Récupère la structure liée à l'utilisateur
    $structure = $user->structure;

    if (!$structure) {
        return response()->json([
            'status' => false,
            'message' => 'Aucune structure liée à cet utilisateur.',
        ], 404);
    }

    // Validation des données
    $validatedData = $request->validate([
        'date_demande' => 'required|date',
        'quantite' => 'required|integer|min:1',
        'statut' => 'required|in:en attente,approuvée,rejetée,urgence',
        'groupe_sanguin_id' => 'required|exists:groupe_sanguins,id',
        'sts_destinataire_id' => 'nullable|exists:structure_transfusion_sanguins,id',
    ]);

    // Création de la demande avec sts_demandeur_id défini automatiquement
    $demande = DemandeRavitaillement::create([
        'date_demande' => $validatedData['date_demande'],
        'quantite' => $validatedData['quantite'],
        'statut' => $validatedData['statut'],
        'groupe_sanguin_id' => $validatedData['groupe_sanguin_id'],
        'sts_demandeur_id' => $structure->id, // Défini automatiquement à partir de la structure de l'utilisateur connecté
        'sts_destinataire_id' => $validatedData['sts_destinataire_id'] ?? null,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Demande de ravitaillement créée avec succès.',
        'data' => $demande
    ], 201);
}

public function demandesReçues()
{
    $user = auth()->user();
    $structureId = $user->structure_transfusion_sanguin_id;

    $demandes = Demande::where('sts_destinataire_id', $structureId)->get();

    return response()->json($demandes);
}

    

    public function show($id)
{
    $demande = DemandeRavitaillement::with(['groupeSanguin', 'stsDemandeur', 'stsDestinataire'])->find($id);

    if (!$demande) {
        return response()->json(['message' => 'Demande non trouvée'], 404);
    }

    return response()->json($demande);
}


    public function update(Request $request, $id)
    {
        $demande = DemandeRavitaillement::find($id);
        if (!$demande) {
            return response()->json(['message' => 'Demande non trouvée'], 404);
        }

        $validator = Validator::make($request->all(), [
            'date_demande' => 'sometimes|date',
            'quantite' => 'sometimes|integer|min:1',
            'statut' => 'sometimes|in:en attente,approuvée,rejetée','urgence',
            'groupe_sanguin_id' => 'sometimes|exists:groupe_sanguins,id',
            'sts_demandeur_id' => 'sometimes|exists:structure_transfusion_sanguins,id',
            'sts_destinataire_id' => 'sometimes|exists:structure_transfusion_sanguins,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $demande->update($request->all());
        return response()->json($demande);
    }

    public function destroy($id)
    {
        $demande = DemandeRavitaillement::find($id);
        if (!$demande) {
            return response()->json(['message' => 'Demande non trouvée'], 404);
        }

        $demande->delete();
        return response()->json(['message' => 'Demande supprimée avec succès']);
    }
   public function approuver($id)
{
    $demande = DemandeRavitaillement::find($id);

    if (!$demande) {
        return response()->json(['message' => 'Demande non trouvée'], 404);
    }

    if ($demande->statut === 'approuvée') {
        return response()->json(['message' => 'Cette demande a déjà été approuvée'], 400);
    }

    if ($demande->statut === 'rejetée') {
        return response()->json(['message' => 'Cette demande a déjà été rejetée et ne peut plus être approuvée'], 400);
    }

    $demande->statut = 'approuvée';
    $demande->save();

    return response()->json(['message' => 'Demande approuvée avec succès'], 200);
}



  public function rejeter($id)
{
    try {
        $demande = DemandeRavitaillement::findOrFail($id);

        if ($demande->statut === 'rejetée') {
            return response()->json(['message' => 'Cette demande a déjà été rejetée'], 400);
        }

        if ($demande->statut === 'approuvée') {
            return response()->json(['message' => 'Cette demande a déjà été approuvée et ne peut plus être rejetée'], 400);
        }

        $demande->statut = 'rejetée';
        $demande->save();

        return response()->json(['message' => 'Demande rejetée avec succès.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors du rejet de la demande.', 'details' => $e->getMessage()], 500);
    }
}

    
}
