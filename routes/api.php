<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    ProduitController,
    CategorieController,
    ArticleController,
    PromotionController,
    UtilisateurController,
    ModePaiementController,
    CommandeController,
    PaiementController,
    LivraisonController,
    DetailCommandeController,
    FraisLivraisonController,
    AuthController,
    StripeController,
    LieuLivraisonController,
    DecoupeController,
    DashboardController
};

// ==================== ROUTES PUBLIQUES (SANS AUTH) ====================
Route::get('/decoupes', [DecoupeController::class, 'index']);
Route::get('/frais_livraisons', [FraisLivraisonController::class, 'index']);
Route::get('/lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('/mode_paiements/actifs', [ModePaiementController::class, 'actifs']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{id}', [ArticleController::class, 'show']);
Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{id}', [ProduitController::class, 'show']);
Route::get('/categories', [CategorieController::class, 'index']);

// Auth publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verify.email');

// Stripe
Route::post('/paiement/stripe/create-session', [StripeController::class, 'createCheckoutSession']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']);

// ==================== ROUTES AUTHENTIFIÉES ====================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/promotions/valider', [PromotionController::class, 'valider']);

    Route::get('utilisateurs/{id}', [UtilisateurController::class, 'show']);
    Route::put('utilisateurs/{id}', [UtilisateurController::class, 'update']);

    Route::get('mesCommandes/{numCommande}/livraison', [LivraisonController::class, 'showByCommandeClient']);
    Route::get('/mesCommandes', [CommandeController::class, 'indexClient']);
    Route::get('/mesCommandes/{id}', [CommandeController::class, 'showClient']);
    Route::post('/commandes', [CommandeController::class, 'store']); 
});

// ==================== ROUTES ADMIN ====================
Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {
    Route::apiResource('articles', ArticleController::class);

    
    // Paiements et commandes
    Route::post('/paiements/{numCommande}/confirmer', [PaiementController::class, 'confirmerPaiement']);
    Route::put('/commandes/{numCommande}', [CommandeController::class, 'update']); 
    Route::get('utilisateurs', [UtilisateurController::class, 'index']); 
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('commandes', CommandeController::class)->except(['store','showClient','indexClient']);

    // Livraisons
    Route::put('livraisons/{id}', [LivraisonController::class, 'update']);
    Route::get('livraisons/{id}', [LivraisonController::class, 'show']);
    Route::get('livraisons', [LivraisonController::class, 'index']);

    // Modes de paiement
    Route::apiResource('mode_paiements', ModePaiementController::class)->except(['actifs']);

    // Promotions
    Route::apiResource('promotions', PromotionController::class);

    Route::post('/send-promo-to-client', [PromotionController::class, 'sendPromoToClient']);
     Route::post('promotions/{id}/restore', [PromotionController::class, 'restore']);
    Route::get('/promotions/deja-envoye/{numPromotion}/{numUtilisateur}', [PromotionController::class, 'checkIfSent']);

    // PRODUITS avec soft delete + restore
    Route::apiResource('produits', ProduitController::class)->except(['index','show']);
    Route::post('produits/{id}/restore', [ProduitController::class, 'restore']);
    
    // CATEGORIES avec soft delete + restore
    Route::apiResource('categories', CategorieController::class)->except(['index','show']);
    Route::post('categories/{id}/restore', [CategorieController::class, 'restore']);
    
    // DECOUPES avec soft delete + restore
    Route::apiResource('decoupes', DecoupeController::class)->except(['index','show']);
    Route::post('decoupes/{id}/restore', [DecoupeController::class, 'restore']);

    // LIEUX DE LIVRAISON avec soft delete + restore
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
    Route::post('lieux_livraison/{id}/restore', [LieuLivraisonController::class, 'restore']);

    // FRAIS DE LIVRAISON avec soft delete + restore
    Route::post('/frais_livraisons/regenerer', [FraisLivraisonController::class, 'regenerer']);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    Route::post('frais_livraisons/{id}/restore', [FraisLivraisonController::class, 'restore']);
    
    Route::get('dashboard/getkpis', [DashboardController::class, 'getKpis']);
    Route::get('/dashboard/kpis', [DashboardController::class, 'kpis']);
    Route::get('/dashboard/sales-over-time', [DashboardController::class, 'salesOverTime']);
    Route::get('/dashboard/sales-by-category', [DashboardController::class, 'salesByCategory']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/top-clients', [DashboardController::class, 'topClients']);
    Route::get('/dashboard/stock-alerts', [DashboardController::class, 'stockAlerts']);
});
