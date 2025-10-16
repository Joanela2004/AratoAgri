<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Article::all(),200);
    }

    /**
     * Store a newly created resource in storage.
     */
    // creer un article
    public function store(Request $request)
    {
        $request->validate([
    'titre' => 'required|string|max:255',
    'description' => 'required|string',
    'contenu' => 'required|string|min:10',
    'image' => 'required|string|max:255',
    'auteur' => 'required|string|max:100',
]);


        $article=Article::create($request->all());
        return response()->json($article,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article=Article::findOrFail($id);
        return response()->json($article,200);
    }

    /**
     * Update the specified resource in storage.
     */

    //modifier article
    public function update(Request $request, string $id)
    {
       $article=Article::findOrFail($id);
       $request->validate([
        'titre'=>'sometimes|string|max:100',
        'description'=>'sometimes|string|max:255',
        'contenu'=>'sometimes|text|min:10',
        'auteur'=>'sometimes|string|max:100',
        'image'=>'sometimes|string',
        'datePublication'=>'sometimes|date'
       ]);

       $article->update($request->all());
       return response()->json($article,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //supprimer un article
    public function destroy(string $id)
    {
        $article=Article::findOrFail($id);
        $article->delete();
        return response()->json(['message'=>'Article supprimé'],200);
    }
}
