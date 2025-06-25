<?php

namespace App\Http\Controllers;

use App\Models\DemandeRavitaillement;
use App\Models\Groupe_sanguin;
use App\Models\BanqueSang;
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
    $user = Auth::user();

    // Vérifie que l'utilisateur a le rôle approprié
    if (!$user->hasRole('Structure_transfusion_sanguin')) {
        return response()->json([
            'message' => 'Seules les structures peuvent consulter les demandes reçues.'
        ], 403);
    }

    // Récupère la structure liée à l'utilisateur
    $structure = $user->structure;

    if (!$structure || !$structure->id) {
        return response()->json([
            'status' => false,
            'message' => 'Aucune structure liée à cet utilisateur.',
        ], 404);
    }

    // Récupère uniquement les demandes reçues
    $demandes = DemandeRavitaillement::with(['stsDemandeur', 'stsDestinataire', 'groupeSanguin'])
        ->where('sts_destinataire_id', $structure->id)
        ->orderByDesc('created_at')
        ->get();

    return response()->json($demandes);
}


// public function demandesReçues()
// {
//     $user = Auth::user();

//     // Vérifie que l'utilisateur a le rôle approprié
//     if (!$user->hasRole('Structure_transfusion_sanguin')) {
//         return response()->json([
//             'message' => 'Seules les structures peuvent effectuer des demandes de ravitaillement.'
//         ], 403);
//     }

//     // Récupère la structure liée à l'utilisateur
//     $structure = $user->structure;

//     if (!$structure || !$structure->id) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Aucune structure liée à cet utilisateur.',
//         ], 404);
//     }

//     $structureId = $structure->id;

//     $demandes = DemandeRavitaillement::with(['stsDemandeur', 'stsDestinataire', 'groupeSanguin'])->get();

//     return response()->json($demandes);
// }



public function demandesEnvoyees()
{
    $user = Auth::user();

    // Vérifie que l'utilisateur a le rôle approprié
    if (!$user->hasRole('Structure_transfusion_sanguin')) {
        return response()->json([
            'message' => 'Seules les structures peuvent consulter les demandes envoyées.'
        ], 403);
    }

    // Récupère la structure liée à l'utilisateur
    $structure = $user->structure;

    if (!$structure || !$structure->id) {
        return response()->json([
            'status' => false,
            'message' => 'Aucune structure liée à cet utilisateur.',
        ], 404);
    }

    // Récupère uniquement les demandes envoyées par la structure connectée
    $demandes = DemandeRavitaillement::with(['stsDemandeur', 'stsDestinataire', 'groupeSanguin'])
        ->where('sts_demandeur_id', $structure->id)
        ->orderByDesc('created_at')
        ->get();

    return response()->json($demandes);
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
        return response()->json(['message' => 'Cette demande a été rejetée et ne peut pas être approuvée'], 400);
    }

    $groupeSanguinId = $demande->groupe_sanguin_id;
    $quantiteDemandee = $demande->quantite;

    // ⚠️ Structure qui ENVOIE le sang (par exemple : Kaolack)
    $structureEnvoyeurId = $demande->sts_destinataire_id;

    // ⚠️ Structure qui REÇOIT le sang (par exemple : Thiès)
    $structureReceveurId = $demande->sts_demandeur_id;

    // 1. Vérifier la banque de sang de l'ENVOYEUR (il doit avoir du stock)
    $banqueEnvoyeur = BanqueSang::where('structure_transfusion_sanguin_id', $structureEnvoyeurId)
                                 ->where('groupe_sanguin_id', $groupeSanguinId)
                                 ->first();

    if (!$banqueEnvoyeur) {
        return response()->json([
            'message' => 'Aucune banque de sang trouvée pour la structure envoyeur et ce groupe sanguin.'
        ], 400);
    }

    if ($banqueEnvoyeur->stock_actuelle < $quantiteDemandee) {
        return response()->json([
            'message' => "Stock insuffisant : vous avez {$banqueEnvoyeur->stock_actuelle} poche(s), mais la demande en nécessite {$quantiteDemandee}."
        ], 400);
    }

    // 2. Déduire le stock de l'envoyeur
    $banqueEnvoyeur->stock_actuelle -= $quantiteDemandee;
    $banqueEnvoyeur->save();

    // 3. Ajouter le stock au receveur
    $banqueReceveur = BanqueSang::firstOrCreate(
        [
            'structure_transfusion_sanguin_id' => $structureReceveurId,
            'groupe_sanguin_id' => $groupeSanguinId
        ],
        [
            'nombre_poche' => 0,
            'stock_actuelle' => 0,
            'date_mise_a_jour' => now(),
            'date_expiration' => now()->addMonth()->toDateString(),
            'heure_expiration' => '23:59',
            'date_dernier_stock' => now()->toDateString(),
            'date_dernier_approvisionnement' => now()->toDateString(),
            'date_dernier_rapprochement' => now()->toDateString(),
            'statut' => 'disponible'
        ]
    );

    $banqueReceveur->stock_actuelle += $quantiteDemandee;
    $banqueReceveur->save();

    // 4. Mettre à jour le statut de la demande
    $demande->statut = 'approuvée';
    $demande->save();

    return response()->json([
        'message' => 'Demande approuvée. Le transfert de sang a été effectué avec succès.'
    ], 200);
}
// public function approuver($id)
// {
//     $demande = DemandeRavitaillement::find($id);

//     if (!$demande) {
//         return response()->json(['message' => 'Demande non trouvée'], 404);
//     }

//     if ($demande->statut === 'approuvée') {
//         return response()->json(['message' => 'Cette demande a déjà été approuvée'], 400);
//     }

//     if ($demande->statut === 'rejetée') {
//         return response()->json(['message' => 'Cette demande a été rejetée et ne peut pas être approuvée'], 400);
//     }

//     $groupeSanguinId = $demande->groupe_sanguin_id;
//     $quantiteDemandee = $demande->quantite;

//     $structureEnvoyeurId = $demande->sts_destinataire_id;
//     $structureReceveurId = $demande->sts_demandeur_id;

//     // Vérification stock...
//     $banqueEnvoyeur = BanqueSang::where('structure_transfusion_sanguin_id', $structureEnvoyeurId)
//                                  ->where('groupe_sanguin_id', $groupeSanguinId)
//                                  ->first();

//     if (!$banqueEnvoyeur) {
//         return response()->json([
//             'message' => 'Aucune banque de sang trouvée pour la structure envoyeur et ce groupe sanguin.'
//         ], 400);
//     }

//     if ($banqueEnvoyeur->stock_actuelle < $quantiteDemandee) {
//         return response()->json([
//             'message' => "Stock insuffisant : vous avez {$banqueEnvoyeur->stock_actuelle} poche(s), mais la demande en nécessite {$quantiteDemandee}."
//         ], 400);
//     }

//     // Mise à jour stocks
//     $banqueEnvoyeur->stock_actuelle -= $quantiteDemandee;
//     $banqueEnvoyeur->save();

//     $banqueReceveur = BanqueSang::firstOrCreate(
//         [
//             'structure_transfusion_sanguin_id' => $structureReceveurId,
//             'groupe_sanguin_id' => $groupeSanguinId
//         ],
//         [
//             'nombre_poche' => 0,
//             'stock_actuelle' => 0,
//             'date_mise_a_jour' => now(),
//             'date_expiration' => now()->addMonth()->toDateString(),
//             'heure_expiration' => '23:59',
//             'date_dernier_stock' => now()->toDateString(),
//             'date_dernier_approvisionnement' => now()->toDateString(),
//             'date_dernier_rapprochement' => now()->toDateString(),
//             'statut' => 'disponible'
//         ]
//     );

//     $banqueReceveur->stock_actuelle += $quantiteDemandee;
//     $banqueReceveur->save();

//     // Mise à jour statut demande
//     $demande->statut = 'approuvée';
//     $demande->save();

//     // 🔔 Notification à la structure demandeuse (receveur)
//     $receveurUser = \App\Models\User::where('structure_transfusion_sanguin_id', $structureReceveurId)->first();

//     if ($receveurUser) {
//         \App\Models\Notification::create([
//             'user_id' => $receveurUser->id,
//             'message' => "Votre demande de {$quantiteDemandee} poche(s) du groupe sanguin {$demande->groupeSanguin->libelle} a été approuvée.",
//             'type' => 'demande_approuvee',
//             'statut' => 'non-lue',
//             'created_at' => now(),
//         ]);
//     }

//     return response()->json([
//         'message' => 'Demande approuvée. Le transfert de sang a été effectué avec succès.'
//     ], 200);
// }


// public function rejeter($id)
// {
//     try {
//         $demande = DemandeRavitaillement::findOrFail($id);

//         if ($demande->statut === 'rejetée') {
//             return response()->json(['message' => 'Cette demande a déjà été rejetée'], 400);
//         }

//         if ($demande->statut === 'approuvée') {
//             return response()->json(['message' => 'Cette demande a déjà été approuvée et ne peut plus être rejetée'], 400);
//         }

//         $demande->statut = 'rejetée';
//         $demande->save();

//         // 🔔 Notification à la structure demandeuse (receveur)
//         $receveurUser = \App\Models\User::where('structure_transfusion_sanguin_id', $demande->sts_demandeur_id)->first();

//         if ($receveurUser) {
//             \App\Models\Notification::create([
//                 'user_id' => $receveurUser->id,
//                 'message' => "Votre demande de {$demande->quantite} poche(s) du groupe sanguin {$demande->groupeSanguin->libelle} a été rejetée.",
//                 'type' => 'demande_rejetee',
//                 'statut' => 'non-lue',
//                 'created_at' => now(),
//             ]);
//         }

//         return response()->json(['message' => 'Demande rejetée avec succès.'], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => 'Erreur lors du rejet de la demande.', 'details' => $e->getMessage()], 500);
//     }
// }




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

   
// public function mesDemandes($type)
// {
//     $user = Auth::user();
//     if (!$user->hasRole('Structure_transfusion_sanguin')) {
//         return response()->json(['message' => 'Non autorisé'], 403);
//     }

//     $structureId = $user->structure->id;

//     $query = DemandeRavitaillement::with(['stsDemandeur', 'stsDestinataire', 'groupeSanguin']);

//     if ($type === 'envoyees') {
//         $query->where('sts_demandeur_id', $structureId);
//     } elseif ($type === 'recues') {
//         $query->where('sts_destinataire_id', $structureId);
//     } else {
//         return response()->json(['message' => 'Type invalide. Utiliser "envoyees" ou "recues".'], 400);
//     }

//     return response()->json($query->get());
// }
}