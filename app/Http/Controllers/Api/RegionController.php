<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
     /**
     * Afficher la liste de toutes les régions.
     */
    public function index()
    {
        return response()->json(Region::all(), 200);
    }

    /**
     * Enregistrer une nouvelle région.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::in([
                    'Dakar', 'Diourbel', 'Fatick', 'Kaffrine', 'Kaolack',
                    'Kédougou', 'Kolda', 'Louga', 'Matam', 'Saint-Louis',
                    'Sédhiou', 'Tambacounda', 'Thiès', 'Ziguinchor',
                ]),
                'unique:regions,libelle',
            ],
        ]);

        $region = Region::create([
            'libelle' => $request->libelle,
        ]);

        return response()->json([
            'message' => 'Région ajoutée avec succès.',
            'region' => $region,
        ], 201);
    }

    /**
     * Afficher une région spécifique.
     */
    public function show($id)
    {
        $region = Region::find($id);

        if (!$region) {
            return response()->json(['message' => 'Région non trouvée.'], 404);
        }

        return response()->json($region, 200);
    }

    /**
     * Mettre à jour une région.
     */
    public function update(Request $request, $id)
    {
        $region = Region::find($id);

        if (!$region) {
            return response()->json(['message' => 'Région non trouvée.'], 404);
        }

        $request->validate([
            'libelle' => [
                'required',
                'string',
                Rule::in([
                    'Dakar', 'Diourbel', 'Fatick', 'Kaffrine', 'Kaolack',
                    'Kédougou', 'Kolda', 'Louga', 'Matam', 'Saint-Louis',
                    'Sédhiou', 'Tambacounda', 'Thiès', 'Ziguinchor',
                ]),
                Rule::unique('regions', 'libelle')->ignore($region->id),
            ],
        ]);

        $region->update([
            'libelle' => $request->libelle,
        ]);

        return response()->json([
            'message' => 'Région mise à jour avec succès.',
            'region' => $region,
        ], 200);
    }

    /**
     * Supprimer une région.
     */
    public function destroy($id)
    {
        $region = Region::find($id);

        if (!$region) {
            return response()->json(['message' => 'Région non trouvée.'], 404);
        }

        $region->delete();

        return response()->json(['message' => 'Région supprimée avec succès.'], 200);
    }
}