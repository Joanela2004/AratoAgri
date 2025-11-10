<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::all();
        return response()->json($categories);
    }

    public function show($id)
    {
        $categorie = Categorie::find($id);
        if (!$categorie) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }
        return response()->json($categorie);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomCategorie' => 'required|string|unique:categories,nomCategorie'
        ]);

        $nom = strtolower($request->nomCategorie);
        $nom = rtrim($nom, 's');

        if (Categorie::whereRaw('LOWER(nomCategorie) = ?', [$nom])->exists()) {
        return response()->json(['message' => 'Cette catégorie existe déjà !'], 422);
    }
        $categorie = Categorie::create([
            'nomCategorie' => $request->nomCategorie
        ]);

        return response()->json($categorie, 201);
    }

    public function update(Request $request, $id)
    {
        $categorie = Categorie::findOrFail($id);
        
        $request->validate([
            'nomCategorie' => 'required|string|unique:categories,nomCategorie'
        ]);

        $categorie->update($request->all());
        return response()->json($categorie);
    }

    public function destroy($id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();
        return response()->json(['message' => 'Catégorie supprimée']);
    }
}
