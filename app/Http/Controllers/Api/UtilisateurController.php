<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Mail\VerificationEmail;

class UtilisateurController extends Controller
{
    public function index()
    {
        $utilisateurs = Utilisateur::with('commandes')->get();
        return response()->json($utilisateurs, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomUtilisateur' => 'required|string|max:100',
            'email' => 'required|email|unique:utilisateurs,email|email:dns', 
            'contact' => 'required|string|max:15',
            'motDePasse' => 'required|string|min:6',
            'role' => 'required|in:admin,client',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageName = time() . '_' . $file->getClientOriginalName();
            $imagePath = $file->storeAs('profiles', $imageName, 'public'); 
        }

        $token = Str::random(60);

        $utilisateur = Utilisateur::create([
            'nomUtilisateur' => $request->nomUtilisateur,
            'email' => $request->email,
            'contact' => $request->contact,
            'motDePasse' => Hash::make($request->motDePasse),
            'role' => $request->role,
            'image' => $imagePath,
            'email_verification_token' => $token,
            'email_verified_at' => null, 
        ]);

        Mail::to($utilisateur->email)->send(new VerificationEmail($utilisateur));

        return response()->json([
            'message' => 'Utilisateur créé. Vérifiez votre email pour confirmation.',
            'utilisateur' => $utilisateur
        ], 201);
    }

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
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $id . ',numUtilisateur|email:dns',
            'contact' => 'sometimes|string|max:15',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['nomUtilisateur', 'contact']);

        if ($request->has('email') && $request->email !== $utilisateur->email) {
            $data['email'] = $request->email;
            $data['email_verified_at'] = null; 
            $data['email_verification_token'] = Str::random(60);
            Mail::to($request->email)->send(new VerificationEmail($utilisateur));
        }

        if ($request->hasFile('image')) {
            if ($utilisateur->image && Storage::disk('public')->exists($utilisateur->image)) {
                Storage::disk('public')->delete($utilisateur->image);
            }
            $path = $request->file('image')->store('profiles', 'public');
            $data['image'] = $path;
        }

        $utilisateur->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'utilisateur' => $utilisateur
        ], 200);
    }

    public function destroy(string $id)
    {
        $utilisateur = Utilisateur::findOrFail($id);

        if ($utilisateur->image && Storage::disk('public')->exists($utilisateur->image)) {
            Storage::disk('public')->delete($utilisateur->image);
        }

        $utilisateur->delete();
        return response()->json(['message' => 'Utilisateur supprimé'], 200);
    }

    public function clientsAvecCommandes()
    {
        $clients = Utilisateur::where('role', 'client')
            ->whereHas('commandes')
            ->withCount('commandes')
            ->get();

        return response()->json($clients, 200);
    }
}
