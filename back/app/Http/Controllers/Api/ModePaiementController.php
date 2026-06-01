<?php
 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModePaiementController extends Controller
{
    public function index()
    {
        $modes = ModePaiement::all();
        return response()->json($modes);
    }

    public function show($id)
    {
        $mode = ModePaiement::find($id);
        if (!$mode) {
            return response()->json(['message' => 'Mode de paiement non trouvé'], 404);
        }
        return response()->json($mode);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomModePaiement' => 'required|string|max:100',
            'actif' => 'sometimes',
            'config' => 'nullable|string',
            'typePaiement'    => 'nullable|string|max:50',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'nomModePaiement' => $validated['nomModePaiement'],
                   'actif' => isset($validated['actif']) ? 
                ($validated['actif'] === 'true' || $validated['actif'] === '1' || $validated['actif'] === true) 
                : true,
        ];

            if (isset($validated['config']) && $validated['config'] !== "") {
            $data['config'] = json_decode($validated['config'], true);
        } else {
             $data['config'] = null;
        }

        if (array_key_exists('typePaiement', $validated)) {
            $data['typePaiement'] = $validated['typePaiement'];
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('modePaiements', 'public');
            $data['image'] = $path;
        }

        $mode = ModePaiement::create($data);

        return response()->json($mode, 201);
    }

    public function update(Request $request, $id)
    {
        $mode = ModePaiement::find($id);
        if (!$mode) {
            return response()->json(['message' => 'Mode de paiement non trouvé'], 404);
        }

        $validated = $request->validate([
            'nomModePaiement' => 'sometimes|required|string|max:100',
            'typePaiement'    => 'nullable|string|max:50',
            'actif'           => 'sometimes|boolean',
            'config'          => 'nullable|string',
            'image'           => 'nullable|image|max:2048',
        ]);

        $data = [];

        if ($request->has('nomModePaiement')) {
            $data['nomModePaiement'] = $validated['nomModePaiement'];
        }
        if ($request->has('typePaiement')) {                    // ← Important !
            $data['typePaiement'] = $validated['typePaiement'];
        }
        if ($request->has('actif')) {
            $data['actif'] = $validated['actif'];
        }
        if ($request->filled('config') || $request->has('config')) {
            $data['config'] = $validated['config'] ? json_decode($validated['config'], true) : null;
        }
        if ($request->hasFile('image')) {
            if ($mode->image) {
                Storage::disk('public')->delete($mode->image);
            }
            $data['image'] = $request->file('image')->store('modePaiements', 'public');
        }

        $mode->update($data);

        return response()->json($mode);
    }

    public function destroy($id)
    {
        $mode = ModePaiement::find($id);
        if (!$mode) {
            return response()->json(['message' => 'Mode de paiement non trouvé'], 404);
        }

        if ($mode->image) {
            Storage::disk('public')->delete($mode->image);
        }

        $mode->delete();

        return response()->json(['message' => 'Mode de paiement supprimé']);
    }
    public function actifs()
{
    return ModePaiement::where('actif', true)->get();
}

}