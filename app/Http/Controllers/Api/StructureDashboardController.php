<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StructureDashboardController extends Controller
{
    public function nombreDeCampagnes() {
    return Campagne::where('structure_transfusion_sanguin_id', auth()->user()->structure_id)->count();
}

public function donsValidés() {
    return Participation::where('status', 'validé')
        ->whereHas('campagne', function ($query) {
            $query->where('structure_transfusion_sanguin_id', auth()->user()->structure_id);
        })->count();
}

public function donateursActifs() {
    return Donateur::whereHas('participations.campagne', function ($query) {
        $query->where('structure_transfusion_sanguin_id', auth()->user()->structure_id);
    })->distinct()->count();
}

public function statsGroupesSanguins() {
    return Donateur::select('groupe_sanguin_id', DB::raw('count(*) as total'))
        ->groupBy('groupe_sanguin_id')
        ->get();
}

}
