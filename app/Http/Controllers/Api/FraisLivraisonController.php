<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FraisLivraison;

class FraisLivraisonController extends Controller
{
    public function index()
    {
        return response()->json(FraisLivraison::all(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'poidsMin' => 'required|numeric|min:0',
            'poidsMax' => 'required|numeric|gt:poidsMin',
            'frais'    => 'required|numeric|min:0',
        ]);

        $intervalMin = $validated['poidsMin'];
        $intervalMax = $validated['poidsMax'];
        $frais       = $validated['frais'];

        $increment = $intervalMax - $intervalMin + 1;

        $ranges = [];
        $count = 1;

        for ($min = $intervalMin; $min <= 1000; $min += $increment) {
            $max = $min + $increment - 1;
            if ($max > 1000) $max = 1000;
            $ranges[] = [
                'poidsMin' => $min,
                'poidsMax' => $max,
                'frais'    => $frais * $count
            ];
            $count++;
        }

        foreach ($ranges as $r) {
            FraisLivraison::create($r);
        }

        return response()->json([
            'message' => 'Tranches générées avec succès',
            'data' => $ranges
        ], 201);
    }

    public function show($id)
    {
        $frais = FraisLivraison::find($id);
        if (!$frais) {
            return response()->json(['message'=>'frais non trouvé'],400);
        }
        return response()->json($frais);
    }

    public function update(Request $request, $id)
    {
        $frais = FraisLivraison::find($id);
        if (!$frais) {
            return response()->json(['message'=>'Frais non trouvé'],404);
        }

        $validated = $request->validate([
            'poidsMin' => 'sometimes|numeric|min:0',
            'poidsMax' => 'sometimes|numeric|gt:poidsMin',
            'frais'    => 'sometimes|numeric|min:0',
        ]);

        $frais->update($validated);

        return response()->json(['message'=>'Frais mis à jour'],200);
    }

    public function destroy($id)
    {
        $frais = FraisLivraison::find($id);
        if (!$frais) {
            return response()->json(['message'=>'Frais non trouvé'],404);
        }

        $frais->delete();

        return response()->json(['message' => 'Frais supprimé']);
    }
}
