<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $user = Auth::user(); // Assure-toi que le token est bien transmis avec Authorization: Bearer {token}

        return response()->json([
            'id' => $user->id,
            'nom' => $user->nom,
            'email' => $user->email,
            'roles' => $user->getRoleNames(), // Spatie
            'organisateur' => $user->organisateur ?? null,
            'structure_transfusion_sanguin' => $user->structure_transfusion_sanguin ?? null,
        ]);
    }
}
