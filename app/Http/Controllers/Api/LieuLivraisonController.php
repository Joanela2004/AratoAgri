<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LieuLivraison;

class LieuLivraisonController extends Controller
{
    public function index()
    {
        // Renvoie tous les lieux actifs (pas besoin de withTrashed ici)
        return response()->json(LieuLivraison::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomLieu'   => 'required|string|max:100',
            'fraisLieu' => 'required|numeric|min:0',
        ]);

        // On cherche si un lieu avec ce nom existe déjà (même supprimé)
        $lieuExistant = LieuLivraison::withTrashed()
            ->where('nomLieu', $request->nomLieu)
            ->first();

        if ($lieuExistant) {
            if ($lieuExistant->trashed()) {
                // LIEU ARCHIVÉ → on propose la restauration
                return response()->json([
                    'message'      => 'The nom lieu has already been taken.',
                    'soft_deleted' => true,
                    'lieu_id'      => $lieuExistant->numLieu,
                    'nomLieu'      => $lieuExistant->nomLieu,
                ], 422);
            }

            // LIEU ACTIF → erreur classique
            return response()->json([
                'message' => 'The nom lieu has already been taken.',
                'errors'  => ['nomLieu' => ['Ce nom de lieu existe déjà.']],
            ], 422);
        }

        // Tout bon → création
        $lieu = LieuLivraison::create($request->only(['nomLieu', 'fraisLieu']));

        return response()->json($lieu, 201);
    }

    public function show($id)
    {
        $lieu = LieuLivraison::find($id);
        if (!$lieu) {
            return response()->json(['message' => 'Lieu non trouvé'], 404);
        }
        return response()->json($lieu, 200);
    }

    public function update(Request $request, $id)
    {
        $lieu = LieuLivraison::find($id);
        if (!$lieu) {
            return response()->json(['message' => 'Lieu non trouvé'], 404);
        }

        $request->validate([
            'nomLieu'   => 'sometimes|string|max:100|unique:lieux_livraison,nomLieu,' . $id . ',numLieu',
            'fraisLieu' => 'sometimes|numeric|min:0',
        ]);

        $lieu->update($request->only(['nomLieu', 'fraisLieu']));

        return response()->json($lieu, 200);
    }

    public function destroy($id)
    {
        $lieu = LieuLivraison::find($id);
        if (!$lieu) {
            return response()->json(['message' => 'Lieu non trouvé'], 404);
        }

        $lieu->delete();

        return response()->json(['message' => 'Lieu supprimé (soft delete)'], 200);
    }

    public function restore($id)
    {
        $lieu = LieuLivraison::withTrashed()->find($id);
        if (!$lieu) {
            return response()->json(['message' => 'Lieu non trouvé'], 404);
        }

        $lieu->restore();

        return response()->json([
            'message' => 'Lieu restauré avec succès !',
            'data'    => $lieu->fresh()
        ], 200);
    }
}