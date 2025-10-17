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
        $request->validate(['nomCategorie'=>'required|string|max:255']);
        $categorie = Categorie::create($request->all());
        return response()->json($categorie, 201);
    }

    public function show(string $id)
    {
        $categorie = Categorie::findOrFail($id);
        return response()->json($categorie, 200);
    }

    public function update(Request $request, string $id)
    {
        $categorie = Categorie::findOrFail($id);
        $request->validate(['nomCategorie'=>'sometimes|string|max:255']);
        $categorie->update($request->all());
        return response()->json($categorie, 200);
    }

    public function destroy(string $id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();
        return response()->json(['message'=>'Categorie supprimée'], 200);
    }
}
