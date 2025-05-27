<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BanqueSang;
use Illuminate\Http\Request;
use App\Models\Groupe_sanguin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class BanqueDeSangController extends Controller
{
    private function getStructureId()
    {
        $user = Auth::user();
        return $user && $user->structure ? $user->structure->id : null;
    }

    // public function index()
    // {
    //     $structureId = $this->getStructureId();

    //     if (!$structureId) {
    //         return response()->json(['message' => 'Accès non autorisé.'], 403);
    //     }

    //     $banques = BanqueSang::where('structure_transfusion_sanguin_id', $structureId)
    //         ->with('groupe_sanguin')
    //         ->get();

    //     return response()->json($banques);
    // }
    // public function index(Request $request)
// {
//      $user = Auth::user();

//     // Vérifie que l'utilisateur a le rôle "Structure_transfusion_sanguin"
//     if (!$user->hasRole('Structure_transfusion_sanguin')) {
//         return response()->json([
//             'message' => 'Seules les structures peuvent consulter leurs campagnes.'
//         ], 403);
//     }

//     // Récupère la structure liée à l'utilisateur connecté
//     $structure = $user->structure;

//     if (!$structure) {
//         return response()->json([
//             'status' => false,
//             'message' => 'Aucune structure liée à cet utilisateur.',
//         ], 404);
//     }

//     $structureId = $user->structure_transfusion_sanguin_id;
//     $capaciteMax = 100; // Tu pourras rendre cela dynamique si tu veux

//     $groupes = Groupe_sanguin::all();
//     $resultats = [];

//     foreach ($groupes as $groupe) {
//         $entrees = DemandeRavitaillement::where('sts_destinataire_id', $structureId)
//             ->where('groupe_sanguin_id', $groupe->id)
//             ->where('statut', 'approuvée')
//             ->sum('quantite');

//         $sorties = DemandeRavitaillement::where('sts_demandeur_id', $structureId)
//             ->where('groupe_sanguin_id', $groupe->id)
//             ->where('statut', 'approuvée')
//             ->sum('quantite');

//         $stock = $entrees - $sorties;
//         $pourcentage = max(0, min(100, intval(($stock / $capaciteMax) * 100)));

//         $niveau = match (true) {
//             $stock <= 4 => 'critical',
//             $stock <= 14 => 'low',
//             $stock <= 49 => 'normal',
//             default => 'full',
//         };

//         $resultats[] = [
//             'type' => $groupe->libelle,
//             'quantity' => max(0, $stock),
//             'level' => $niveau,
//             'percentage' => $pourcentage,
//         ];
//     }

//     return response()->json($resultats);
// }

public function index()
{
    $user = Auth::user();

    if (!$user || !$user->hasRole('Structure_transfusion_sanguin')) {
        return response()->json(['message' => 'Accès non autorisé.'], 403);
    }

    $structureId = $user->structure->id;

    $banques = BanqueSang::where('structure_transfusion_sanguin_id', $structureId)
        ->with('groupe_sanguin')
        ->get();

    return response()->json($banques);
}



public function stocks()
{
    try {
        $user = Auth::user();

        if (!$user || !$user->hasRole('Structure_transfusion_sanguin')) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $structureId = $user->structure->id ?? null;

        if (!$structureId) {
            return response()->json(['message' => 'Structure non trouvée.'], 404);
        }

        $stocks = DB::table('banque_sangs')
            ->join('groupe_sanguins', 'banque_sangs.groupe_sanguin_id', '=', 'groupe_sanguins.id')
            ->select(
                'groupe_sanguins.libelle as type',
                'banque_sangs.stock_actuelle as quantity'
            )
            ->where('structure_transfusion_sanguin_id', $structureId)
            ->get();

        $stocks = $stocks->map(function ($item) {
            $maxCapacity = 100;
            $item->percentage = round(($item->quantity / $maxCapacity) * 100);
            $item->level = match (true) {
                $item->percentage < 20 => 'critical',
                $item->percentage < 50 => 'low',
                default => 'normal',
            };
            return $item;
        });

        return response()->json($stocks);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur serveur', 'error' => $e->getMessage()], 500);
    }
}


   public function store(Request $request)
{
    $request->validate([
        'nombre_poche' => 'required|integer',
        'stock_actuelle' => 'required|integer',
        'date_mise_a_jour' => 'required|date',
        'statut' => 'required|string',
        'date_expiration' => 'required|date',
        'heure_expiration' => 'required',
        'date_dernier_stock' => 'required|date',
        'date_dernier_approvisionnement' => 'required|date',
        'date_dernier_rapprochement' => 'required|date',
        'groupe_sanguin_id' => 'required|exists:groupe_sanguins,id',
    ]);

    $user = Auth::user();
    $structureId = $user->structure->id;

    $banque = BanqueSang::create([
        'nombre_poche' => $request->nombre_poche,
        'stock_actuelle' => $request->stock_actuelle,
        'date_mise_a_jour' => $request->date_mise_a_jour,
        'statut' => $request->statut,
        'date_expiration' => $request->date_expiration,
        'heure_expiration' => $request->heure_expiration,
        'date_dernier_stock' => $request->date_dernier_stock,
        'date_dernier_approvisionnement' => $request->date_dernier_approvisionnement,
        'date_dernier_rapprochement' => $request->date_dernier_rapprochement,
        'groupe_sanguin_id' => $request->groupe_sanguin_id,
        'structure_transfusion_sanguin_id' => $structureId,
    ]);

    return response()->json($banque, 201);
}

    public function show($id)
    {
        $structureId = $this->getStructureId();

        $banque = BanqueSang::where('id', $id)
            ->where('structure_transfusion_sanguin_id', $structureId)
            ->with('groupe_sanguin')
            ->firstOrFail();

        return response()->json($banque);
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'nombre_poche' => 'required|integer',
        'stock_actuelle' => 'required|integer',
        'date_mise_a_jour' => 'required|date',
        'statut' => 'required|string',
        'date_expiration' => 'required|date',
        'heure_expiration' => 'required',
        'date_dernier_stock' => 'required|date',
        'date_dernier_approvisionnement' => 'required|date',
        'date_dernier_rapprochement' => 'required|date',
        'groupe_sanguin_id' => 'required|exists:groupe_sanguins,id',
    ]);

    $user = Auth::user();
    $structureId = $user->structure->id;

    $banque = BanqueSang::where('id', $id)
        ->where('structure_transfusion_sanguin_id', $structureId)
        ->first();

    if (!$banque) {
        return response()->json([
            'message' => 'Stock non trouvé ou accès non autorisé.'
        ], 404);
    }

    $banque->update([
        'nombre_poche' => $request->nombre_poche,
        'stock_actuelle' => $request->stock_actuelle,
        'date_mise_a_jour' => $request->date_mise_a_jour,
        'statut' => $request->statut,
        'date_expiration' => $request->date_expiration,
        'heure_expiration' => $request->heure_expiration,
        'date_dernier_stock' => $request->date_dernier_stock,
        'date_dernier_approvisionnement' => $request->date_dernier_approvisionnement,
        'date_dernier_rapprochement' => $request->date_dernier_rapprochement,
        'groupe_sanguin_id' => $request->groupe_sanguin_id,
    ]);

    return response()->json($banque, 200);
}


    public function destroy($id)
    {
        $structureId = $this->getStructureId();

        $banque = BanqueSang::where('id', $id)
            ->where('structure_transfusion_sanguin_id', $structureId)
            ->firstOrFail();

        $banque->delete();

        return response()->json(['message' => 'Banque supprimée avec succès.']);
    }
}
