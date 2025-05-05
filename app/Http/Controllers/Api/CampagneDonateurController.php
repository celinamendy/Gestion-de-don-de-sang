<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampagneDonateurController extends Controller
{
    public function index()
    {
        $campagnes  = Campagne::all();
        return response()->json([
            'status' => true,
            'message' => 'La liste des campagnes',
            'data' => $campagnes
        ]);
    }
    public function show($id)
    {
        $campagne = Campagne::find($id);
        if (!$campagne) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Détails de la campagne',
            'data' => $campagne
        ]);
    }

}
