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
        'nomCategorie' => 'required|string'
       
    ]);

    $nom = $request->nomCategorie;
    $nomNormalise = strtolower(rtrim($nom, 's')); 

    $existingActive = Categorie::where(function ($query) use ($nom, $nomNormalise) {
        $query->where('nomCategorie', $nom)
              ->orWhereRaw('LOWER(TRIM(TRAILING "s" FROM nomCategorie)) = ?', [$nomNormalise]);
    })->whereNull('deleted_at') 
      ->first();

    if ($existingActive) {
        return response()->json([
            'message' => 'The nom categorie has already been taken.',
            'errors'  => ['nomCategorie' => ['Cette catégorie existe déjà.']]
        ], 422);
    }

    $existingDeleted = Categorie::onlyTrashed()
        ->where(function ($query) use ($nom, $nomNormalise) {
            $query->where('nomCategorie', $nom)
                  ->orWhereRaw('LOWER(TRIM(TRAILING "s" FROM nomCategorie)) = ?', [$nomNormalise]);
        })->first();

    if ($existingDeleted) {
        return response()->json([
            'message'        => 'Categorie soft deleted',
            'soft_deleted'   => true,
            'categorie_id'   => $existingDeleted->numCategorie,
            'categorie_nom'  => $existingDeleted->nomCategorie
        ], 409); // 409 Conflict = ressource existe mais dans un état particulier
    }

    // 3. Si rien trouvé → création normale
    $categorie = Categorie::create([
        'nomCategorie' => $nom
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
    $categorie = Categorie::find($id);

    if (!$categorie) {
        return response()->json(['message' => 'Catégorie non trouvée'], 404);
    }

    $categorie->delete(); // Soft delete, produits liés non affectés

    return response()->json(['message' => 'Catégorie supprimée avec succès'], 200);
}

public function restore($id)
{
    $categorie = Categorie::withTrashed()->find($id);
    if (!$categorie) {
        return response()->json(['message' => 'Catégorie non trouvée'], 404);
    }
    $categorie->restore();
    return response()->json(['message' => 'Catégorie restaurée avec succès', 'data' => $categorie]);
}

}
