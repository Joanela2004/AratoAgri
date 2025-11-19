<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Decoupe;

class DecoupeController extends Controller
{
    public function index()
    {
        $decoupes = Decoupe::all();
        return response()->json($decoupes, 200);
    }

    public function show($id)
    {
        $decoupe = Decoupe::findOrFail($id);
        return response()->json($decoupe, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomDecoupe' => 'required|string|max:255',
            'coefficient' => 'required|numeric|min:0.01'
        ]);

        $decoupe = Decoupe::create([
            'nomDecoupe' => $request->nomDecoupe,
            'coefficient' => $request->coefficient
        ]);

        return response()->json($decoupe, 201);
    }

    public function update(Request $request, $id)
    {
        $decoupe = Decoupe::findOrFail($id);

        $request->validate([
            'nomDecoupe' => 'sometimes|string|max:255',
            'coefficient' => 'sometimes|numeric|min:0.01'
        ]);

        $decoupe->update($request->only(['nomDecoupe','coefficient']));

        return response()->json($decoupe, 200);
    }

    public function destroy($id)
    {
        $decoupe = Decoupe::findOrFail($id);
        $decoupe->delete();
        return response()->json(['message'=>'Découpe supprimée'], 200);
    }
}
