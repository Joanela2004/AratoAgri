<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index()
    {
        return response()->json(Article::all(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'          => 'required|string|max:100',
            'description'    => 'required|string|max:255',
            'contenu'        => 'required|string|min:10',
            'auteur'         => 'required|string|max:100',
            'datePublication'=> 'required|date',
            'image'          => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = $request->only(['titre', 'description', 'contenu', 'auteur', 'datePublication']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article = Article::create($data);

        return response()->json($article, 201);
    }

    public function update(Request $request, string $id)
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'titre'          => 'sometimes|required|string|max:100',
            'description'    => 'sometimes|required|string|max:255',
            'contenu'        => 'sometimes|required|string|min:10',
            'auteur'         => 'sometimes|required|string|max:100',
            'datePublication'=> 'sometimes|required|date',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = $request->only(['titre', 'description', 'contenu', 'auteur', 'datePublication']);

        if ($request->hasFile('image')) {

            // Supprime l'ancienne image
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }

            // Enregistre la nouvelle image
            $data['image'] = $request->file('image')->store('articles', 'public');
        }

        $article->update($data);

        return response()->json($article, 200);
    }

    public function show(string $id)
    {
        return response()->json(Article::findOrFail($id));
    }

    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);

        // suppression correcte
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        $article->delete();

        return response()->json(['message' => 'Article supprimé']);
    }
}
