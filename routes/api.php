<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\DecoupeController;
use App\Http\Controllers\Api\FraisLivraisonController;
use App\Http\Controllers\Api\LieuLivraisonController;
use App\Http\Controllers\Api\ModePaiementController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\StripeController;

// ==================== ROUTES PUBLIQUES (SANS AUTH) ====================
Route::get('/decoupes', [DecoupeController::class, 'index']);
Route::get('/frais_livraisons', [FraisLivraisonController::class, 'index']);
Route::get('/lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('/modes_paiement/actifs', [ModePaiementController::class, 'actifs']); // CRUCIAL

Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{id}', [ProduitController::class, 'show']);
Route::get('/categories', [CategorieController::class, 'index']);

// Auth publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');

// Stripe

Route::post('/create-checkout-session', [StripeController::class, 'createCheckoutSession']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);


// ==================== ROUTES PROTÉGÉES ====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);


     Route::get('utilisateurs/{id}', [UtilisateurController::class, 'show']);
    Route::post('utilisateurs/{id}', [UtilisateurController::class, 'update']); 

  

   
    // Commandes
    Route::get('/commandes/client', [CommandeController::class, 'indexClient']);
    Route::get('/commandes/client/{id}', [CommandeController::class, 'showClient']);
    Route::post('/commandes', [CommandeController::class, 'store']); // ← La plus importante

    // Admin only (si tu veux protéger plus tard)
    Route::prefix('admin')->group(function () {

        Route::post('/paiements/{numCommande}/confirmer', [PaiementController::class, 'confirmerPaiement']);
       Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    Route::get('utilisateurs', [UtilisateurController::class, 'index']); 
    Route::post('send-promo-to-client', [PromotionController::class, 'sendEmailToUsers']);  
   
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('commandes', CommandeController::class)->except(['store']);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    
    Route::put('livraisons/{id}', [LivraisonController::class, 'update']);
    Route::get('livraisons/{id}', [LivraisonController::class, 'show']);
    Route::get('livraisons', [LivraisonController::class, 'index']);
    
    Route::apiResource('modes_paiement', ModePaiementController::class)->except(['actifs']);
    
    // Routes de catalogue Admin
    Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
    Route::apiResource('decoupes', DecoupeController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategorieController::class)->except(['index', 'show']);
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::apiResource('promotions', PromotionController::class)->except(['index', 'show']);
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
});
});