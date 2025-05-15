<?php

namespace App\Http\Controllers;

use App\Models\DemandeRavitaillement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DemandeRavitaillementController extends Controller
{
    public function index()
    {
        $demandes = DemandeRavitaillement::all();
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
       $request->validate([
        'date_demande' => 'required|date',
        'quantite' => 'required|integer|min:1',
        'statut' => 'required|in:en attente,approuvée,rejetée,urgence',
        'groupe_sanguin_id' => 'nullable|exists:groupe_sanguins,id',
        'sts_demandeur_id' => 'required|exists:structure_transfusion_sanguins,id',
        'sts_destinataire_id' => 'nullable|exists:structure_transfusion_sanguins,id',
    ]);

    $demande = DemandeRavitaillement::create($request->all());

    return response()->json($demande, 201);
}
    

    public function show($id)
    {
        $demande = DemandeRavitaillement::find($id);
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
    
}
