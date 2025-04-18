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


}
