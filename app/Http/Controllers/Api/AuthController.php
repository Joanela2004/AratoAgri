<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetailPanier;
use App\Models\Utilisateur;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Mail\VerificationEmail;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'nomUtilisateur' => 'required|string|max:100',
            'email' => 'required|email|unique:utilisateurs,email', 
            'contact' => 'required|string|max:15',
            'motDePasse' => 'required|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $user = new Utilisateur();
        $user->nomUtilisateur = $validatedData['nomUtilisateur'];
        $user->email = $validatedData['email'];
        $user->contact = $validatedData['contact'];
        $user->motDePasse = Hash::make($validatedData['motDePasse']);
        $user->role = 'client';
        $user->email_verified_at = null;
        $user->email_verification_token = Str::random(60);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $user->image = $path;
        } else {
            $user->image = null;
        }

        $user->save();

        try {
            Mail::to($user->email)->send(new VerificationEmail($user));
        } catch (\Exception $e) {
            \Log::error("Erreur d'envoi d'email de vérification: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Inscription réussie. Vérifiez votre email pour confirmation.',
            'user' => [
                'id' => $user->numUtilisateur,
                'nomUtilisateur' => $user->nomUtilisateur,
                'email' => $user->email,
                'contact' => $user->contact,
                'role' => $user->role,
                'image' => $user->image,
            ],
        ], 201);
    }
public function verifyEmail($token)
{
    $user = Utilisateur::where('email_verification_token', $token)->first();

    if (!$user) {
        return redirect(env('FRONTEND_URL') . '/?email_verification=failed');
    }

    $user->email_verified_at = now();
    $user->email_verification_token = null;
    $user->save();

    return redirect(env('FRONTEND_URL') . '/?email_verification=success');
}


    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'motDePasse' => 'required|string',
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->motDePasse, $user->motDePasse)) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        if ($user->role !== 'admin' && is_null($user->email_verified_at)) {
    return response()->json(['message' => 'Veuillez vérifier votre email avant de vous connecter.'], 403);
}


        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;
 $localCartItems = $request->input('local_cart_items', []);
 $this->mergeLocalCart($user->numUtilisateur, $localCartItems);       
 return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->numUtilisateur,
                'nomUtilisateur' => $user->nomUtilisateur,
                'email' => $user->email,
                'contact' => $user->contact,
                'role' => $user->role,
                'image' => $user->image,
            ]
        ]);
    }

    // Déconnexion
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie.'], 200);
    }

    // Changement de mot de passe
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed'
        ]);

        if (!Hash::check($request->current_password, $user->motDePasse)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 400);
        }

        $user->motDePasse = Hash::make($request->new_password);
        $user->save();
        $user->tokens()->delete();

        return response()->json(['message' => 'Mot de passe mis à jour, veuillez vous reconnecter.'], 200);
    }
    protected function getPanierClient(int $userId)
    {
        return Panier::with('detailsPaniers.produit')
            ->where('numUtilisateur', $userId)
            ->where('statut', 'en_cours')
            ->first();
    }
    protected function mergeLocalCart(int $userId, array $localItems)
    {
        if (empty($localItems)) {
            return;
        }

        // 1. Récupérer ou créer le panier "en cours" BDD de l'utilisateur
        $panier = Panier::firstOrCreate(
            ['numUtilisateur' => $userId, 'statut' => 'en_cours']
        );

        foreach ($localItems as $item) {
            // Assurez-vous que le front-end envoie 'numProduit' et 'poids'
            $numProduit = $item['numProduit'] ?? null;
            $poids = (float)($item['poids'] ?? 0); 
            
            $produit = Produit::find($numProduit);
            
            if (!$produit || $poids <= 0) {
                continue;
            }

            // 2. Chercher si l'article existe déjà dans le panier BDD
            $detail = DetailPanier::firstOrNew([
                'numPanier' => $panier->numPanier,
                'numProduit' => $numProduit,
            ]);

            // 3. Déterminer la nouvelle quantité totale (fusion)
            $poidsActuel = $detail->exists ? (float)$detail->poids : 0;
            $newPoids = $poidsActuel + $poids;

            // 4. Mettre à jour les détails du panier (avec vérification de stock)
            if ($newPoids <= $produit->poids) { // $produit->poids est le stock disponible
                $detail->poids = $newPoids; // Mise à jour de la quantité/poids
                $detail->prixUnitaire = $produit->prix;
                $detail->sousTotal = $produit->prix * $newPoids;
                $detail->save();
            } else {
                                 if ($poidsActuel < $produit->poids) {
                      $detail->poids = $produit->poids; // Ajouter jusqu'au stock maximum
                      $detail->prixUnitaire = $produit->prix;
                      $detail->sousTotal = $produit->prix * $produit->poids;
                      $detail->save();
                 }
            }
        }
    }
}
