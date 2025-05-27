<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StructureTransfusionSanguin;


class StructureTransfusionSanguinController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
       {
        return StructureTransfusionSanguin::all();
    }
    
//     public function index()
// {
//     $user = auth()->user();
//     $sts_id = $user->structure_transfusion_sanguin_id;

//     $structures = StructureTransfusionSanguin::where('id', '!=', $sts_id)->get();

//     return response()->json($structures);
// }

// public function index()
// {
//     $user = auth()->user();
//     $sts_id = $user->structure_transfusion_sanguin_id;

//     $structures = StructureTransfusionSanguin::where('id', '!=', $sts_id)->get();

//     return response()->json($structures);
// }
public function structuresDestinataires()
{
    $user = auth()->user();
    $sts_id = $user->structure_transfusion_sanguin_id;

    $structures = StructureTransfusionSanguin::where('id', '!=', $sts_id)->get();

    return response()->json($structures);
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
