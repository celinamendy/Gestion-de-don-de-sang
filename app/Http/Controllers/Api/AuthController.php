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
        //  dd($request->all());

        Log::info('Tentative d\'inscription', ['type' => $request->type, 'email' => $request->email]);

        $validator = Validator::make($request->all(), [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'string', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'type' => ['required', 'in:admin,donateur,organisateur,structure_transfusion_sanguin'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'adresse' => ['nullable', 'string'],
            'sexe' => ['nullable', 'in:M,F'],
            'date_naissance' => ['nullable', 'date'],
            'poids' => ['nullable', 'numeric'],
            'antecedent_medicament' => ['nullable', 'string'],
            'date_dernier_don' => ['nullable', 'date'],
            'groupe_sanguin_id' => ['nullable', 'exists:groupe_sanguin,id'],
            'nom_responsable' => ['nullable', 'string'],
            'type_organisation' => ['nullable', 'string'],
            'type_entite' => ['nullable', 'string'],
            'structure_transfusion_sanguin_id' => ['nullable', 'exists:structure_transfusion_sanguins,id'],
        ]);

        if ($validator->fails()) {
            Log::warning('Échec de validation à l\'inscription', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'nom' => $request->input('nom'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'telephone' => $request->input('telephone'),
            'region_id' => $request->input('region_id'),
        ]);

        Log::info('Utilisateur créé', ['user_id' => $user->id]);

        $role = $request->input('type');
        $user->assignRole($role);

        switch ($role) {
            case 'admin':
                $admin = Admin::create(['user_id' => $user->id]);
                Log::info('Admin créé', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'admin' => $admin], 201);

            case 'donateur':
                $donateur = Donateur::create([
                    'user_id' => $user->id,
                    'adresse' => $request->input('adresse'),
                    'sexe' => $request->input('sexe'),
                    'date_naissance' => $request->input('date_naissance'),
                    'poids' => $request->input('poids'),
                    'antecedent_medicament' => $request->input('antecedent_medicament'),
                    'date_dernier_don' => $request->input('date_dernier_don'),
                    'groupe_sanguin_id' => $request->input('groupe_sanguins_id'),
                ]);
                Log::info('Donateur créé', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'donateur' => $donateur], 201);

            case 'organisateur':
                $organisateur = Organisateur::create([
                    'user_id' => $user->id,
                    'adresse' => $request->input('adresse'),
                    'nom_responsable' => $request->input('nom_responsable'),
                    'type_organisation' => $request->input('type_organisation'),
                    'structure_transfusion_sanguin_id' => $request->input('structure_transfusion_sanguin_id'),
                ]);
                Log::info('Organisateur créé', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'organisateur' => $organisateur], 201);

            case 'structure':
                $structure = StructureTransfusionSanguin::create([
                    'user_id' => $user->id,
                    'nom_responsable' => $request->input('nom_responsable'),
                    'adresse' => $request->input('adresse'),
                    'type_entite' => $request->input('type_entite'),
                ]);
                Log::info('Structure créée', ['user_id' => $user->id]);
                return response()->json(['user' => $user, 'structure' => $structure], 201);

            default:
                Log::error('Type utilisateur inconnu', ['type' => $role]);
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
