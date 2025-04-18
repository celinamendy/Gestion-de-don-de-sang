<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Donateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DonateurController extends Controller
{
    /**
     * Liste de tous les donateurs.
     */
    public function index()
    {
        $donateurs = Donateur::with('user')->get();
        return response()->json([
            'status' => true,
            'message' => 'Liste des donateurs récupérée avec succès.',
            'data' => $donateurs
        ]);
    }

    /**
     * Création d'un donateur avec utilisateur associé.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'adresse' => 'required|string',
            'date_naissance' => 'required|date',
            'sexe' => 'required|in:M,F',
            'groupe_sanguin_id' => 'required|exists:groupe_sanguins,id',
            'poids' => 'required|string',
            'antecedent_medicament' => 'nullable|in:Aucun,Maladie chronique,hépathite,anémier,autre',
            'date_dernier_don' => 'nullable|date',
            'groupe_sanguins_id' => 'required|exists:groupe_sanguins,id',
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $donateur = Donateur::create([
            'user_id' => $user->id,
            'adresse' => $request->adresse,
            'date_naissance' => $request->date_naissance,
            'sexe' => $request->sexe,
            'groupe_sanguin_id' => $request->groupe_sanguin_id,
            'poids' => $request->poids,
            'antecedent_medicament' => $request->antecedent_medicament ?? 'Aucun',
            'date_dernier_don' => $request->date_dernier_don,
            'groupe_sanguin_id' => $request->groupe_sanguins_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Donateur créé avec succès.',
            'data' => $donateur
        ], 201);
    }

    /**
     * Afficher les informations d'un donateur par ID utilisateur.
     */
    public function getDonateurByUserId($userId)
    {
        $donateur = Donateur::where('user_id', $userId)->with('user')->first();

        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Donateur non trouvé.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Donateur récupéré avec succès.',
            'data' => $donateur
        ]);
    }

    /**
     * Récupérer le donateur connecté.
     */
    public function getAuthenticatedDonateur()
    {
        $userId = auth()->id();
        $donateur = Donateur::where('user_id', $userId)->with('user')->first();

        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun donateur trouvé pour cet utilisateur.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Donateur récupéré avec succès.',
            'data' => $donateur
        ]);
    }

    /**
     * Mise à jour d'un donateur.
     */
    public function update(Request $request, $id)
    {
        $donateur = Donateur::find($id);

        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Donateur non trouvé.'
            ], 404);
        }

        $request->validate([
            'adresse' => 'sometimes|string',
            'date_naissance' => 'sometimes|date',
            'sexe' => 'sometimes|in:M,F',
            'groupe_sanguin_id' => 'sometimes|exists:groupe_sanguins,id',
            'poids' => 'sometimes|string',
            'antecedent_medicament' => 'sometimes|in:Aucun,Maladie chronique,hépathite,anémier,autre',
            'date_dernier_don' => 'nullable|date',
            'groupe_sanguins_id' => 'sometimes|exists:groupe_sanguins,id',
            'nom' => 'sometimes|string|max:255',
            'prenom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $donateur->user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $donateur->update($request->only([
            'adresse',
            'date_naissance',
            'sexe',
            'groupe_sanguin_id',
            'poids',
            'antecedent_medicament',
            'date_dernier_don',
            'groupe_sanguins_id'
        ]));

        $user = $donateur->user;
        if ($user) {
            $user->nom = $request->input('nom', $user->nom);
            $user->prenom = $request->input('prenom', $user->prenom);
            $user->email = $request->input('email', $user->email);
            if ($request->filled('password')) {
                $user->password = bcrypt($request->password);
            }
            $user->save();
        }

        return response()->json([
            'status' => true,
            'message' => 'Donateur et utilisateur mis à jour avec succès.',
            'data' => [
                'donateur' => $donateur,
                'user' => $user
            ]
        ]);
    }

    /**
     * Supprimer un donateur.
     */
    public function destroy(Donateur $donateur)
    {
        $user = $donateur->user;
        if ($user) {
            $user->delete();
        }
        $donateur->delete();

        return response()->json([
            'status' => true,
            'message' => 'Donateur supprimé avec succès.'
        ]);
    }
  

}
