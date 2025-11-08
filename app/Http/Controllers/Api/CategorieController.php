<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function index()
    {
        return response()->json(Categorie::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomCategorie' => 'required|string|max:255'
        ]);

        $categorie = Categorie::create([
            'nomCategorie' => $request->nomCategorie
        ]);

        return response()->json($categorie, 201);
    }

    public function show(string $id)
    {
        return response()->json(Categorie::findOrFail($id), 200);
    }

    public function update(Request $request, string $id)
    {
        $categorie = Categorie::findOrFail($id);
        $request->validate(['nomCategorie' => 'sometimes|string|max:255']);
        $categorie->update($request->only('nomCategorie'));

        return response()->json($categorie, 200);
    }

    public function destroy(string $id)
    {
        Categorie::findOrFail($id)->delete();
        return response()->json(['message' => 'Catégorie supprimée'], 200);
    }
}
