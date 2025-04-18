<?php

namespace App\Http\Controllers;
use App\Models\Participation;
use Illuminate\Support\Facades\Validator;


use Illuminate\Http\Request;

class ParticipationController extends Controller
{

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'campagne_id' => 'required|exists:campagnes,id',
        'statut' => 'required|in:en attente,acceptée,refusée',
        'date_participation' => 'required|date',
        'lieu_participation' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $participation = Participation::create($request->all());

    return response()->json($participation, 201);
}
   // Historique des dons d’un utilisateur
   public function historiqueDons($userId)
   {
       $dons = Participation::select('date_participation', 'lieu_participation')
           ->where('user_id', $userId)
           ->where('statut', 'acceptée')
           ->orderBy('date_participation', 'desc')
           ->get();

       return response()->json($dons);
   }

   // Historique des campagnes auxquelles il a participé
   public function historiqueCampagnes($userId)
   {
       $campagnes = Participation::with('campagne')
           ->where('user_id', $userId)
           ->where('statut', 'acceptée')
           ->orderBy('date_participation', 'desc')
           ->get();

       return response()->json($campagnes);
   }

    // Récupérer les participants d'une campagne
    public function participantsParCampagne($campagneId)
{
    $campagne = Campagne::with(['participations.donateur'])->find($campagneId);

    if (!$campagne) {
        return response()->json([
            'status' => false,
            'message' => 'Campagne non trouvée',
        ], 404);
    }

    $participants = $campagne->participations->map(function ($participation) {
        return [
            'id' => $participation->donateur->id,
            'nom' => $participation->donateur->nom ?? 'Inconnu',
            'email' => $participation->donateur->email ?? null,
            'groupe_sanguin' => $participation->donateur->groupe_sanguin->libelle ?? null,
            'date_participation' => $participation->created_at->toDateString(),
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Liste des participants récupérée avec succès',
        'data' => $participants,
    ]);
}

}

