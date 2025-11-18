<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LieuLivraison;

class LieuLivraisonController extends Controller
{
    public function index()
    {
        return response()->json(LieuLivraison::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomLieu' => 'required|string|max:100|unique:lieux_livraison,nomLieu',
            'fraisLieu' => 'required|numeric|min:0'
        ]);

        $lieu = LieuLivraison::create($validated);

        return response()->json($lieu, 201);
    }

    public function show($id)
    {
        $lieu = LieuLivraison::find($id);
        if (!$lieu) return response()->json(['message' => 'Lieu non trouvé'], 404);
        return response()->json($lieu, 200);
    }

    public function update(Request $request, $id)
    {
        $lieu = LieuLivraison::find($id);
        if (!$lieu) return response()->json(['message' => 'Lieu non trouvé'], 404);

        $validated = $request->validate([
            'nomLieu' => 'sometimes|string|max:100|unique:lieux_livraison,nomLieu,' . $id,
            'fraisLieu' => 'sometimes|numeric|min:0'
        ]);

        $lieu->update($validated);

        return response()->json($lieu, 200);
    }

    public function destroy($id)
    {
        $lieu = LieuLivraison::find($id);
        if (!$lieu) return response()->json(['message' => 'Lieu non trouvé'], 404);

        $lieu->delete();

        return response()->json(['message' => 'Lieu supprimé'], 200);
    }
}
