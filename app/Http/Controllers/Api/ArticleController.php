<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        return response()->json(Article::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'=>'required|string|max:255',
            'description'=>'required|string|max:255',
            'contenu'=>'required|string|min:10',
            'image'=>'required|string|max:255',
            'auteur'=>'required|string|max:100',
        ]);

        $article = Article::create($request->all());
        return response()->json($article, 201);
    }

    public function show(string $id)
    {
        $article = Article::findOrFail($id);
        return response()->json($article, 200);
    }

    public function update(Request $request, string $id)
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'titre'=>'sometimes|string|max:255',
            'description'=>'sometimes|string|max:255',
            'contenu'=>'sometimes|string|min:10',
            'image'=>'sometimes|string|max:255',
            'auteur'=>'sometimes|string|max:100',
            'datePublication'=>'sometimes|date',
        ]);

        $article->update($request->all());
        return response()->json($article, 200);
    }

    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        $article->delete();
        return response()->json(['message'=>'Article supprimé'], 200);
    }
}
