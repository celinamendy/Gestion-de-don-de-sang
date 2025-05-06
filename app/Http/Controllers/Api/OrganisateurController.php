<?php

namespace App\Http\Controllers\API;
use App\Models\Campagne;
use App\Models\Organisateur;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrganisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     * Récupérer le donateur connecté.
     */
    public function getAuthenticatedOrganisateur()
    {
        $userId = auth()->id();
        $organisateur = Organisateur::where('user_id', $userId)->with('user')->first();

        if (!$organisateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun organisateur  trouvé pour cet utilisateur.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'organisateur bien récupéré avec succès.',
            'data' => $organisateur
        ]);
    }

    public function getByUserId($id)
{
    $user = Auth::user();
    $organisateur = Organisateur::where('user_id', $user->id)->first();

    if (!$organisateur) {
        return response()->json(['message' => 'Organisateur non trouvé.'], 404);
    }

    return response()->json([
        'status' => true,
        'organisateur' => $organisateur
    ]);
}


public function mescampagnes()
{
    $user = Auth::user();

    if (!$user->hasRole('Organisateur')) {
        return response()->json([
            'status' => false,
            'message' => 'Seuls les organisateurs peuvent voir leurs campagnes.'
        ], 403);
    }

    $organisateur = $user->organisateur;

    if (!$organisateur) {
        return response()->json([
            'status' => false,
            'message' => 'Aucun organisateur lié à cet utilisateur.'
        ], 404);
    }

    $campagnes = Campagne::with('structureTransfusionSanguin')
        ->where('organisateur_id', $organisateur->id)
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Liste des campagnes récupérées avec succès.',
        'data' => $campagnes
    ], 200);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
