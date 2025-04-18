<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BanqueSang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanqueDeSangController extends Controller
{
    //  Voir les banques de la structure connectée
    public function index()
    {
        $structureId = Auth::id();

        $banques = BanqueSang::where('structure_transfusion_sanguin_id', $structureId)->with('groupeSanguin')->get();

        return response()->json($banques);
    }

    // Créer une nouvelle banque
    public function store(Request $request)
    {
        $request->validate([
            'nombre_poche' => 'required|integer',
            'stock_actuelle' => 'required|integer',
            'date_mise_a_jour' => 'required|date',
            'statut' => 'in:disponible,indisponible',
            'date_expiration' => 'required|string',
            'heure_expiration' => 'required|string',
            'date_dernier_stock' => 'required|string',
            'date_dernier_approvisionnement' => 'required|string',
            'date_dernier_rapprochement' => 'required|string',
            'groupe_sanguin_id' => 'required|exists:groupe_sanguins,id',
        ]);

        $banque = BanqueSang::create([
            'nombre_poche' => $request->nombre_poche,
            'stock_actuelle' => $request->stock_actuelle,
            'date_mise_a_jour' => $request->date_mise_a_jour,
            'statut' => $request->statut ?? 'disponible',
            'date_expiration' => $request->date_expiration,
            'heure_expiration' => $request->heure_expiration,
            'date_dernier_stock' => $request->date_dernier_stock,
            'date_dernier_approvisionnement' => $request->date_dernier_approvisionnement,
            'date_dernier_rapprochement' => $request->date_dernier_rapprochement,
            'groupe_sanguin_id' => $request->groupe_sanguin_id,
            'structure_transfusion_sanguin_id' => Auth::id(),
        ]);

        return response()->json($banque, 201);
    }

    // Voir le détail d’une banque
    public function show($id)
    {
        $banque = BanqueSang::where('id', $id)
            ->where('structure_transfusion_sanguin_id', Auth::id())
            ->with('groupeSanguin')
            ->firstOrFail();

        return response()->json($banque);
    }

    // Modifier une banque
    public function update(Request $request, $id)
    {
        $banque = BanqueSang::where('id', $id)
            ->where('structure_transfusion_sanguin_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'nombre_poche' => 'integer',
            'stock_actuelle' => 'integer',
            'date_mise_a_jour' => 'date',
            'statut' => 'in:disponible,indisponible',
            'date_expiration' => 'string',
            'heure_expiration' => 'string',
            'date_dernier_stock' => 'string',
            'date_dernier_approvisionnement' => 'string',
            'date_dernier_rapprochement' => 'string',
            'groupe_sanguin_id' => 'exists:groupe_sanguins,id',
        ]);

        $banque->update($request->only([
            'nombre_poche',
            'stock_actuelle',
            'date_mise_a_jour',
            'statut',
            'date_expiration',
            'heure_expiration',
            'date_dernier_stock',
            'date_dernier_approvisionnement',
            'date_dernier_rapprochement',
            'groupe_sanguin_id',
        ]));

        return response()->json($banque);
    }

    //  Supprimer une banque
    public function destroy($id)
    {
        $banque = BanqueSang::where('id', $id)
            ->where('structure_transfusion_sanguin_id', Auth::id())
            ->firstOrFail();

        $banque->delete();

        return response()->json(['message' => 'Banque supprimée avec succès.']);
    }
}
