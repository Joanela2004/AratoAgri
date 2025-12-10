<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Produit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\VerificationEmail;
use App\Mail\ResetPasswordCodeMail;
class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        // Validation des données
        $validatedData = $request->validate([
            'nomUtilisateur' => 'required|string|max:100',
            'email' => 'required|email|unique:utilisateurs,email',
            'contact' => 'required|string|max:15',
            'motDePasse' => 'required|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        // Création de l'utilisateur
        $user = new Utilisateur();
        $user->nomUtilisateur = $validatedData['nomUtilisateur'];
        $user->email = $validatedData['email'];
        $user->contact = $validatedData['contact'];
        $user->motDePasse = Hash::make($validatedData['motDePasse']);
        $user->role = 'client';
        $user->email_verified_at = null;
        $user->email_verification_token = Str::random(60);

        // Gestion de l'image (si uploadée)
        if ($request->hasFile('image')) {
            $user->image = $request->file('image')->store('profiles', 'public');
        }

        $user->save();

        // Envoi de l'email de vérification
        try {
            Mail::to($user->email)->send(new VerificationEmail($user));
        } catch (\Exception $e) {
            \Log::error("Erreur envoi email vérification: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Inscription réussie. Vérifiez votre email.',
            'user' => $user->only(['numUtilisateur', 'nomUtilisateur', 'email', 'contact', 'role', 'image'])
        ], 201);
    }

    /**
     * Vérifie l'email avec le token envoyé
     * Active le compte si le token est valide
     */
    public function verifierEmail($token)
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

    /**
     * Connexion de l'utilisateur
     * Vérifie les identifiants, génère un token, et fusionne le panier local
     */
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
            return response()->json(['message' => 'Veuillez vérifier votre email'], 403);
        }

        // Supprime les anciens tokens
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Fusion du panier local (ce que l'utilisateur avait hors ligne)
        $panierLocal = $request->input('local_cart_items', []);
        $this->fusionnerPanierLocal($user->numUtilisateur, $panierLocal);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only(['numUtilisateur', 'nomUtilisateur', 'email', 'contact', 'role', 'image'])
        ]);
    }

    /**
     * Fusionne le panier local (hors ligne) avec le panier en session
     * Additionne les quantités pour éviter de perdre le panier à la connexion
     */
    protected function fusionnerPanierLocal(int $userId, array $panierLocal)
    {
        if (empty($panierLocal)) {
            return; // Rien à fusionner si le panier local est vide
        }

        $panierFusionne = [];

        foreach ($panierLocal as $item) {
            $numProduit = $item['numProduit'] ?? null;
            $poids = (float)($item['poids'] ?? 0);

            // Ignorer si produit invalide ou poids <= 0
            if (!$numProduit || $poids <= 0) {
                continue;
            }

            $produit = Produit::find($numProduit);
            if (!$produit) {
                continue; // Produit supprimé ? On l'ignore
            }

            // Si le produit est déjà dans le panier fusionné, additionner les poids
            if (isset($panierFusionne[$numProduit])) {
                $panierFusionne[$numProduit]['poids'] += $poids;
            } else {
                $panierFusionne[$numProduit] = [
                    'numProduit' => $numProduit,
                    'poids' => $poids,
                    'optionDecoupe' => $item['cuttingOption'] ?? 'entier',
                    'prixApresDecoupe' => $item['prixApresDecoupe'] ?? $produit->prix,
                ];
            }

            // Limiter au stock disponible
            if ($panierFusionne[$numProduit]['poids'] > $produit->poids) {
                $panierFusionne[$numProduit]['poids'] = $produit->poids;
            }
        }

        // Stocker le panier fusionné dans la session (temporaire)
        session(['panier_fusionne_' . $userId => $panierFusionne]);
    }

    /**
     * Récupère le panier fusionné pour un utilisateur
     * Utilisé par CommandeController si le panier frontal est vide
     */
    public static function recupererPanierFusionne(int $userId)
    {
        return session('panier_fusionne_' . $userId, []);
    }

    /**
     * Vide le panier fusionné après une commande
     * Appelé par CommandeController
     */
    public static function viderPanierFusionne(int $userId)
    {
        session()->forget('panier_fusionne_' . $userId);
    }

    /**
     * Déconnexion de l'utilisateur
     * Supprime le token actif
     */
    public function deconnexion(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }

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

        return response()->json(['message' => 'Mot de passe mis à jour. Reconnectez-vous.'], 200);
    }
        /**
     * Mot de passe oublié → envoi d'un code à 6 chiffres par email
     */
    public function motDePasseOublie(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:utilisateurs,email'
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        // Générer un code à 6 chiffres
        $code = sprintf("%06d", mt_rand(0, 999999));

        $user->code_reinitialisation = $code;
        $user->code_reinitialisation_expire_le = now()->addMinutes(10);
        $user->save();

        try {
            Mail::to($user->email)->send(new ResetPasswordCodeMail($code));
        } catch (\Exception $e) {
            \Log::error("Erreur envoi code réinitialisation: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'envoi du code'], 500);
        }

        return response()->json([
            'message' => 'Code de réinitialisation envoyé avec succès',
            'email'   => $user->email
        ]);
    }

   
    public function reinitialiserMotDePasse(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:utilisateurs,email',
            'code'     => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Utilisateur::where('email', $request->email)->first();

        // Vérification du code
        if ($user->code_reinitialisation !== $request->code) {
            return response()->json(['message' => 'Code de vérification incorrect'], 400);
        }

        // Vérification de l'expiration
        if (now()->greaterThan($user->code_reinitialisation_expire_le)) {
            $user->code_reinitialisation = null;
            $user->code_reinitialisation_expire_le = null;
            $user->save();

            return response()->json(['message' => 'Ce code a expiré. Veuillez refaire une demande.'], 400);
        }

        // Tout est OK → on change le mot de passe
        $user->motDePasse = Hash::make($request->password);
        $user->code_reinitialisation = null;
        $user->code_reinitialisation_expire_le = null;
        $user->save();

        // On révoque tous les tokens existants (sécurité)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Votre mot de passe a été réinitialisé avec succès ! Vous pouvez vous connecter.'
        ]);
    }
}