<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promotion;
class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Promotion::all(),200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomPromotion'=>'required|string|max:100',
            'codePromo'=>'required|string|max:100',
            'valeur'=>'required|numeric|min:0',
            'dateDebut'=>'required|date',
            'dateFin'=>'required|date|after_or_equal:dateDebut',
            'codePromo'=>'nullable|string|max:50',
            'statutPromotion'=>'required|string|max:50',
            
        ]);

        $promotion = Promotion::create($request->all());
        return $response()->json($promotion,201);

    }

    /**
     * Display the specified resource.
     */

    //afficher une promotion
    public function show(string $id)
    {
        $promotion=Promotion::findOrFail($id);
        return $response()->json($promotion,200);
    }

    /**
     * Update the specified resource in storage.
     */

    //modifier promotion
    public function update(Request $request, string $id)
    {
       $promotion=Promotion::findOrFail($id);
       $request->validate([
        'nomPromotion'=>'sometimes|string|max:100',
        'codePromo'=>'nullable|string|unique:promotions,codePromo|max:100',
        'valeur'=>'sometimes|numeric|min:0',
        'dateDebut'=>'sometimes|date',
        'dateFin'=>'sometimes|date|after_or_equal:dateDebut',
        'statutPromotion'=>'sometimes|string|max:50'
       ]);
       
       $promotion->update($request->all());
       return $response()->json($promotion,200);
    }
    /**
     * Remove the specified resource from storage.
     */
    //supprimer une promotion
    public function destroy(string $id)
    {
     $promotion=Promotion::findOrFail($id);
     $promotion->delete();
     return response()->json(['message'=>'promotion supprimée']);
    }
}
