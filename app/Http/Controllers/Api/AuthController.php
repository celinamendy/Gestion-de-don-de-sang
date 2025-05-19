<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donateur;
use App\Models\Organisateur;
use App\Models\Admin;
use App\Models\StructureTransfusionSanguin;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $token = JWTAuth::fromUser($user);
        // Définir les variables avant de les utiliser
        $organisateur = $user->organisateur;  // Récupérer l'organisateur si l'utilisateur en est un
        $donateur = $user->donateur;          // Récupérer le donateur si l'utilisateur en est un
        $admin = $user->admin;                // Récupérer l'admin si l'utilisateur en est un
        $structure_transfusion_sanguin = $user->structure;  // Récupérer la structure si l'utilisateur en est un

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'email' => $user->email,
                'telephone' => $user->telephone,
                'region_id' => $user->region_id,
                'organisateur_id' => optional($organisateur)->id,
                'donateur_id' => optional($donateur)->id,
                'admin_id' => optional($admin)->id,
                'structure_transfusion_sanguin_id' => optional($structure_transfusion_sanguin)->id,
            ],
            'roles' => $user->getRoleNames(),
        ]);
        
    }


    public function register(Request $request)
    {
        Log::info('Tentative d\'inscription', ['type' => $request->type, 'email' => $request->email]);

        $type = $request->input('type');

        // Base des règles
        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'type' => ['required', Rule::in(['admin', 'donateur', 'organisateur', 'structure_transfusion_sanguin'])],
            'telephone' => ['nullable', 'string', 'max:20'],
            'region_id' => ['required', 'exists:regions,id'],
        ];

        // Règles supplémentaires selon le type
        if ($type === 'donateur') {
            $rules = array_merge($rules, [
                'adresse' => ['nullable', 'string'],
                'sexe' => ['nullable', 'in:M,F'],
                'date_naissance' => ['nullable', 'date'],
                'poids' => ['nullable', 'numeric'],
                'antecedent_medicament' => ['nullable', 'string'],
                'date_dernier_don' => ['nullable', 'date'],
                'groupe_sanguin_id' => ['required', 'exists:groupe_sanguins,id'],
            ]);
        }

        if ($type === 'organisateur') {
            $rules = array_merge($rules, [
                'adresse' => ['nullable', 'string'],
                'nom_responsable' => ['nullable', 'string'],
                'type_organisation' => ['nullable', 'string'],
                'structure_transfusion_sanguin_id' => ['required', 'exists:structure_transfusion_sanguins,id'],
            ]);
        }

        if ($type === 'structure_transfusion_sanguin') {
            $rules = array_merge($rules, [
                'adresse' => ['nullable', 'string'],
                'nom_responsable' => ['nullable', 'string'],
                'type_entite' => 'required|in:hôpital,poste de santé,Clinique,autre',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::warning('Échec de validation', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Création de l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'telephone' => $request->telephone,
            'region_id' => $request->region_id,
        ]);

        $user->assignRole($type);
        Log::info('Utilisateur créé', ['user_id' => $user->id]);

        // Création du profil selon le rôle
        switch ($type) {
            case 'admin':
                $admin = Admin::create(['user_id' => $user->id]);
                Log::info('Admin créé', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'admin' => $admin], 201);

            case 'donateur':
                $donateur = Donateur::create([
                    'user_id' => $user->id,
                    'adresse' => $request->adresse,
                    'sexe' => $request->sexe,
                    'date_naissance' => $request->date_naissance,
                    'poids' => $request->poids,
                    'antecedent_medicament' => $request->antecedent_medicament,
                    'date_dernier_don' => $request->date_dernier_don,
                    'groupe_sanguin_id' => $request->groupe_sanguin_id,
                ]);
                Log::info('Donateur créé', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Inscription réussie.',
                    'data' => [
                        'user' => $user,
                        'donateur' => $donateur
                    ]
                ], 201);


            case 'organisateur':
                $organisateur = Organisateur::create([
                    'user_id' => $user->id,
                    'adresse' => $request->adresse,
                    'nom_responsable' => $request->nom_responsable,
                    'type_organisation' => $request->type_organisation,
                    'structure_transfusion_sanguin_id' => $request->structure_transfusion_sanguin_id,
                ]);
                Log::info('Organisateur créé', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'organisateur' => $organisateur], 201);

            case 'structure_transfusion_sanguin':
                $structure = StructureTransfusionSanguin::create([
                    'user_id' => $user->id,
                    'adresse' => $request->adresse,
                    'nom_responsable' => $request->nom_responsable,
                    'type_entite' => $request->type_entite,
                ]);
                Log::info('Structure créée', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'structure_transfusion_sanguin' => $structure], 201);

            default:
                Log::error('Type utilisateur inconnu', ['type' => $type]);
                return response()->json(['message' => 'Type utilisateur non reconnu.'], 400);
        }
    }
    public function logout()
    {
        Log::info('Déconnexion de l\'utilisateur', ['user_id' => auth()->id()]);
        auth()->logout();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function refresh()
    {
        try {
            $token = auth()->refresh();
            Log::info('Token rafraîchi');
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'user' => auth()->user(),
                'expires_in' => env('JWT_TTL', 60) * 60
            ]);
        } catch (JWTException $e) {
            Log::error('Erreur lors du rafraîchissement du token', ['exception' => $e->getMessage()]);
            return response()->json(['message' => 'Impossible de rafraîchir le token'], 500);
        }
    }

    public function profile()
    {
        $user = auth()->user();
        Log::info('Chargement du profil', ['user_id' => $user->id]);

        return response()->json([
            'user' => $user,
            'region' => $user->region ?? null,
            'roles' => $user->getRoleNames(),
            'donateur' => $user->donateur ?? null,
            'organisateur' => $user->organisateur ?? null,
            'structure' => $user->structure ?? null,
            'admin' => $user->admin ?? null,
        ]);
    }
}
