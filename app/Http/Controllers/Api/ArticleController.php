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
        'titre' => 'required|string|max:100',
        'description' => 'required|string|max:255',
        'contenu' => 'required|string|min:10',
        'image' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048', // validation fichier
        'auteur' => 'required|string|max:100',
    ]);

    $articleData = $request->all();

    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/articles', $filename);
        $articleData['image'] = $filename;
    }

    $article = Article::create($articleData);
    return response()->json($article, 201);
}

public function update(Request $request, string $id)
{
    $article = Article::findOrFail($id);

    $request->validate([
        'titre' => 'sometimes|string|max:100',
        'description' => 'sometimes|string|max:255',
        'contenu' => 'sometimes|string|min:10',
        'image' => 'sometimes|image|mimes:jpg,jpeg,png,gif|max:2048',
        'auteur' => 'sometimes|string|max:100',
        'datePublication' => 'sometimes|date',
    ]);

    $articleData = $request->all();

    if ($request->hasFile('image')) {
               if ($article->image) {
            \Storage::delete('public/articles/' . $article->image);
        }
        $image = $request->file('image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/articles', $filename);
        $articleData['image'] = $filename;
    }

    $article->update($articleData);
    return response()->json($article, 200);
}


    public function show(string $id)
    {
        $article = Article::findOrFail($id);
        return response()->json($article, 200);
    }

       

    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        $article->delete();
        return response()->json(['message'=>'Article supprimé'], 200);
    }
}
