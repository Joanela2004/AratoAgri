<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModePaiement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModePaiementController extends Controller
{
    public function index()
    {
        return response()->json(ModePaiement::all());
    }

    public function show($id)
    {
        $mode = ModePaiement::find($id);
        if(!$mode) return response()->json(['message' => 'Mode de paiement non trouvé'], 404);
        return response()->json($mode);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomModePaiement' => 'required|string|max:100',
            'solde' => 'nullable|numeric',
            'numeroCarte' => 'nullable|string|max:20',
            'nomTitulaire' => 'nullable|string|max:100',
            'dateExpiration' => 'nullable|date',
            'numeroCompte' => 'nullable|string|max:50',
            'typeMobile' => 'nullable|string|max:50', 
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        }

        $mode = ModePaiement::create($request->all());
        return response()->json($mode, 201);
    }

    public function update(Request $request, $id)
    {
        $mode = ModePaiement::find($id);
        if(!$mode) return response()->json(['message' => 'Mode de paiement non trouvé'], 404);

        $mode->update($request->all());
        return response()->json($mode);
    }

    public function destroy($id)
    {
        $mode = ModePaiement::find($id);
        if(!$mode) return response()->json(['message' => 'Mode de paiement non trouvé'], 404);

        $mode->delete();
        return response()->json(['message' => 'Mode de paiement supprimé']);
    }
}
