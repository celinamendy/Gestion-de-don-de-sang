<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Groupe_sanguin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class GroupeSanguinController extends Controller
{
    public function index()
    {
        return response()->json(Groupe_sanguin::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                Rule::in(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'Tous les groupes']),
                'unique:groupe_sanguins,libelle',
            ],
        ]);

        $groupe = Groupe_sanguin::create($validated);
        return response()->json($groupe, 201);
    }

    public function show($id)
    {
        $groupe = Groupe_sanguin::find($id);

        if (!$groupe) {
            return response()->json(['message' => 'Groupe sanguin non trouvé.'], 404);
        }

        return response()->json($groupe);
    }

    public function update(Request $request, $id)
    {
        $groupe = Groupe_sanguin::find($id);

        if (!$groupe) {
            return response()->json(['message' => 'Groupe sanguin non trouvé.'], 404);
        }

        $validated = $request->validate([
            'libelle' => [
                'required',
                Rule::in(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-', 'Tous les groupes']),
                Rule::unique('groupe_sanguins')->ignore($groupe->id),
            ],
        ]);

        $groupe->update($validated);
        return response()->json($groupe);
    }

    public function destroy($id)
    {
        $groupe = Groupe_sanguin::find($id);

        if (!$groupe) {
            return response()->json(['message' => 'Groupe sanguin non trouvé.'], 404);
        }

        $groupe->delete();
        return response()->json(['message' => 'Groupe sanguin supprimé avec succès.']);
    }
}
