<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FraisLivraison;
use Illuminate\Support\Facades\DB;

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

    $poidsMin = $validated['poidsMin'];
    $poidsMax = $validated['poidsMax'];

    $exists = FraisLivraison::where('poidsMin', $poidsMin)
                            ->where('poidsMax', $poidsMax)
                            ->exists();

    if ($exists) {
        return response()->json([
            'conflict' => true,
            'message'  => "La tranche {$poidsMin} - {$poidsMax} kg existe déjà.",
            'type'     => 'active'
        ], 409); 
      }
    $trashed = FraisLivraison::onlyTrashed()
                             ->where('poidsMin', $poidsMin)
                             ->where('poidsMax', $poidsMax)
                             ->first();

    if ($trashed) {
        return response()->json([
            'soft_deleted' => true,
            'frais_id'     => $trashed->numFrais ?? $trashed->id,
            'poids_range'  => "{$poidsMin} - {$poidsMax} kg"
        ], 409);
    }

    $frais = FraisLivraison::create($validated);
    return response()->json($frais, 201);
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
    public function restore($id)
{
    $frais = FraisLivraison::withTrashed()->find($id);
    if (!$frais) return response()->json(['message' => 'Frais non trouvé'], 404);

    $frais->restore();
    return response()->json(['message' => 'Frais restauré', 'data' => $frais]);
}
public function regenerer(Request $request)
{
    $request->validate([
        'poidsMin' => 'required|numeric|min:0',
        'poidsMax' => 'required|numeric|gt:poidsMin',
        'frais'    => 'required|numeric|min:0',
    ]);

    $poidsMin   = (float) $request->poidsMin;
    $poidsMax   = (float) $request->poidsMax;
    $fraisBase  = (float) $request->frais;

    $intervalle     = $poidsMax - $poidsMin + 1;
    $poidsActuel    = $poidsMin;
    $multiplicateur = 1;

    DB::beginTransaction();
    try {
        // ON REMPLACE truncate() PAR delete() → ça garde la transaction active !
        FraisLivraison::query()->delete(); 
        // OU si tu veux supprimer définitivement même les soft-deleted :
        // FraisLivraison::withTrashed()->forceDelete();

        while ($poidsActuel <= 1000) {
            $min = $poidsActuel;
            $max = $poidsActuel + $intervalle - 1;
            if ($max > 1000) $max = 1000;

            FraisLivraison::create([
                'poidsMin' => $min,
                'poidsMax' => $max,
                'frais'    => $fraisBase * $multiplicateur,
            ]);

            $poidsActuel = $max + 1;
            $multiplicateur++;
        }

        DB::commit();

        return response()->json([
            'success'        => true,
            'message'        => 'Toutes les tranches ont été regénérées avec succès !',
            'total_tranches' => FraisLivraison::count(),
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Regénération frais échouée : ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur lors de la régénération.',
        ], 500);
    }
}

}
