<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categorie;
class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    //Recupere tous les donnees de la table categorie
    public function index()
    {
         return response()->json(Categorie::all(),200);
    }

    /**
     * Store a newly created resource in storage.
     */

    // creer une categorie
    public function store(Request $request)
    {
        $request->validate([
          'nomCategorie'=>'required|string|max:255'  
        ]);
        $categorie=Categorie::create($request->all());
        return response()->json($categorie,201);
    }

    /**
     * Display the specified resource.
     */
    //afficher une categorie
    public function show(string $id)
    {
      $categorie=Categorie::findOrFail($id);
      return response()->json($categorie,200);  
    }

    /**
     * Update the specified resource in storage.
     */

    //modifier une categorie
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nomCategorie'=>'sometimes|string|max:255'
        ]);
        $categorie=Categorie::findOrFail($id);
        $categorie->update($request->all());

        return response()->json($categorie,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //supprimer un produit
    public function destroy(string $id)
    {
        $categorie=Categorie::findOrFail($id);
        $categorie->delete();
        return response()->json(['message'=>'Categorie supprimée'],200);
    }
}
