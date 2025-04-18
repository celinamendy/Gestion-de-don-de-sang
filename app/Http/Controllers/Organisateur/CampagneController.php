<!-- <?php
namespace App\Http\Controllers\Organisateur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Campagne;

class CampagneController extends Controller
{
    public function index()
    {
        $campagnes = Campagne::all();
        return response()->json($campagnes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|string',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'lieu' => 'required|string',

        ]);

        $campagne = Campagne::create($validated);
        return response()->json($campagne, 201);
    }

    public function show($id)
    {
        $campagne = Campagne::findOrFail($id);
        return response()->json($campagne);
    }

    public function update(Request $request, $id)
    {
        $campagne = Campagne::findOrFail($id);

        $campagne->update($request->all());
        return response()->json($campagne);
    }

    public function destroy($id)
    {
        $campagne = Campagne::findOrFail($id);
        $campagne->delete();
        return response()->json(['message' => 'Campagne supprimée']);
    }
} -->
