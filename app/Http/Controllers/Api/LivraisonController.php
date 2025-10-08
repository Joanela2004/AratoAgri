<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Livraison;
class LivraisonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $livraison = Livraison::with('commande')->get();
        return response()->json($livraison,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'numCommande'=>'required|exists:commandes,numCommande',
            'dateExpeditiom'=> 'nullable|date',
            'dateLivraison' => 'nullable|date',
             'modeLivraison' => 'required|string|max:50',
              'statutLivraison' => 'required|string|max:50',
              'transporteur' => 'required|string|max:100'

        ]);
        $livraison=Livraison::create($request->all());
        return response()->json($livraison->load('commande'),200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $livraison=Livraison::with('commande')->findOrFail($id);
        return response()->json($livraison,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $livraison = Livraison::findOrFail($id);
        $request->validate([
            'numCommande'=>'sometimes|exists:commandes,numCommande',
            'dateExpeditiom'=> 'nullable|date',
            'dateLivraison' => 'nullable|date',
             'modeLivraison' => 'sometimes|string|max:50',
              'statutLivraison' => 'sometimes|string|max:50',
              'transporteur' => 'sometimes|string|max:100'
        ]);
        $livraison->update($request->all());
        return response()->json($livraison->load('commande'),200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $livraison = Livraison::findOrFail($id);
        $livraison->delete();
        return response()->json(['message'=>'Livraison supprimée'],200);
    }
}
