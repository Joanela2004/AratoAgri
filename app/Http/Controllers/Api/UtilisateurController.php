<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UtilisateurController extends Controller
{
    // Lister tous les utilisateurs
    public function index()
    {
        $utilisateurs = Utilisateur::with('commandes')->get();
        return response()->json($utilisateurs, 200);
    }

    // Créer un utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'nomUtilisateur' => 'required|string|max:100',
            'email' => 'required|email|unique:utilisateurs,email',
            'contact' => 'required|string|max:15',
            'motDePasse' => 'required|string|min:6',
            'role' => 'required|in:admin,client',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('profiles', $imageName, 'public');
            $imagePath = 'profiles/' . $imageName; // stocker le chemin relatif
        }

        $utilisateur = Utilisateur::create([
            'nomUtilisateur' => $request->nomUtilisateur,
            'email' => $request->email,
            'contact' => $request->contact,
            'motDePasse' => Hash::make($request->motDePasse),
            'role' => $request->role,
            'image' => $imagePath,
        ]);

        return response()->json($utilisateur->load('commandes'), 201);
    }

    // Afficher un utilisateur
    public function show(string $id)
    {
        $utilisateur = Utilisateur::with('commandes')->findOrFail($id);
        return response()->json($utilisateur, 200);
    }

   public function update(Request $request, string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        $request->validate([
            'nomUtilisateur' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $id . ',numUtilisateur',
            'contact' => 'sometimes|string|max:15',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['nomUtilisateur', 'email', 'contact']);
        if ($request->hasFile('image')) {
            if ($utilisateur->image && Storage::disk('public')->exists($utilisateur->image)) {
                Storage::disk('public')->delete($utilisateur->image);
            }

          $path = $request->file('image')->store('profiles', 'public');            $data['image'] = $path;
        }

        $utilisateur->update($data);

        return response()->json($utilisateur, 200);
    }

    // Supprimer un utilisateur
    public function destroy(string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->image && Storage::disk('public')->exists($utilisateur->image)) {
            Storage::disk('public')->delete($utilisateur->image);
        }

        $utilisateur->delete();
        return response()->json(['message' => 'Utilisateur supprimé'], 200);
    }

    // Clients avec commandes
    public function clientsAvecCommandes()
    {
        $clients = Utilisateur::where('role', 'client')
            ->whereHas('commandes')
            ->withCount('commandes')
            ->get();

        return response()->json($clients, 200);
    }

   
}


