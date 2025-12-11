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
    MvolaController,
    StripeController,
    LieuLivraisonController,
    DecoupeController,
    DashboardController
};

// ====================== ROUTES PUBLIQUES (SANS AUTH) ======================

// Données de référence
Route::get('/decoupes', [DecoupeController::class, 'index']);
Route::get('/frais_livraisons', [FraisLivraisonController::class, 'index']);
Route::get('/lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('/mode_paiements/actifs', [ModePaiementController::class, 'actifs']);

// Contenu public
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{id}', [ArticleController::class, 'show']);

// Produits et catégories
Route::get('/produits', [ProduitController::class, 'index']);
Route::get('/produits/{id}', [ProduitController::class, 'show']);
Route::get('/categories', [CategorieController::class, 'index']);
Route::get('/categories/{id}', [CategorieController::class, 'show']);


// ==================== AUTHENTIFICATION PUBLIQUE ====================

// Inscription / Connexion / Vérification email
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/verify-email/{token}', [AuthController::class, 'verifierEmail'])->name('verify.email');

// Mot de passe oublié (code à 6 chiffres)
Route::post('/mot-de-passe-oublie', [AuthController::class, 'motDePasseOublie']);
Route::post('/reinitialiser-mot-de-passe', [AuthController::class, 'reinitialiserMotDePasse']);

// Paiements externes
Route::post('/paiement/stripe/create-session', [StripeController::class, 'createCheckoutSession']);
Route::post('/stripe/webhook', [StripeController::class, 'webhook']); // Attention : à protéger en prod
Route::post('/promotions/auto', [PromotionController::class, 'appliquerAuto']);

Route::get('/paiement/mvola/create-token', [MvolaController::class, 'generateToken']);
Route::post('/paiement/mvola/pay', [MvolaController::class, 'pay']);

// ====================== ROUTES PROTÉGÉES (UTILISATEUR CONNECTÉ) ======================
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'deconnexion']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Profil utilisateur
    Route::get('utilisateurs/{id}', [UtilisateurController::class, 'show']);
    Route::put('utilisateurs/{id}', [UtilisateurController::class, 'update']);

    // Commandes client
    Route::get('/mesCommandes', [CommandeController::class, 'indexClient']);
    Route::get('/mesCommandes/{id}', [CommandeController::class, 'showClient']);
    Route::post('/commandes', [CommandeController::class, 'store']);
    Route::patch('/commandes/{referenceCommande}/mode-paiement', [CommandeController::class, 'updateModePaiement']);
    Route::delete('/mesCommandes/{referenceCommande}', [CommandeController::class, 'destroyClient']);
    
    // Promotions
    Route::post('/promotions/valider', [PromotionController::class, 'valider']);

    // Livraison client
    Route::get('mesCommandes/{numCommande}/livraison', [LivraisonController::class, 'showByCommandeClient']);
});

// ====================== ROUTES ADMIN (auth + IsAdmin) ======================
Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {

    // Déconnexion admin (déjà dans le groupe auth, mais on garde pour clarté)
    Route::post('/logout', [AuthController::class, 'deconnexion']);

    // Articles
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);

    // Utilisateurs
    Route::get('utilisateurs', [UtilisateurController::class, 'index']);

    // Commandes & Paiements admin
    Route::post('/paiements/{numCommande}/confirmer', [PaiementController::class, 'confirmerPaiement']);
    Route::apiResource('paiements', PaiementController::class);
    Route::put('/commandes/{numCommande}', [CommandeController::class, 'update']);
    Route::apiResource('commandes', CommandeController::class)->except(['store', 'showClient', 'indexClient']);
    Route::get('/paiements/commande/{referenceCommande}', [PaiementController::class, 'getPaiementByCommande']);

    // Livraisons
    Route::apiResource('livraisons', LivraisonController::class);

    // Modes de paiement
    Route::apiResource('mode_paiements', ModePaiementController::class)->except(['actifs']);

    // Promotions
    Route::apiResource('promotions', PromotionController::class);
    Route::post('/send-promo-to-client', [PromotionController::class, 'sendPromoToClient']);
    Route::post('promotions/{id}/restore', [PromotionController::class, 'restore']);
    Route::get('/promotions/deja-envoye/{numPromotion}/{numUtilisateur}', [PromotionController::class, 'checkIfSent']);

    // Produits + restore
    Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
    Route::post('produits/{id}/restore', [ProduitController::class, 'restore']);

    // Catégories + restore
    Route::apiResource('categories', CategorieController::class)->except(['index']);
    Route::post('categories/{id}/restore', [CategorieController::class, 'restore']);

    // Découpes + restore
    Route::apiResource('decoupes', DecoupeController::class)->except(['index', 'show']);
    Route::post('decoupes/{id}/restore', [DecoupeController::class, 'restore']);

    // Lieux de livraison + restore
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
    Route::post('lieux_livraison/{id}/restore', [LieuLivraisonController::class, 'restore']);

    // Frais de livraison
    Route::post('/frais_livraisons/regenerer', [FraisLivraisonController::class, 'regenerer']);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    Route::post('frais_livraisons/{id}/restore', [FraisLivraisonController::class, 'restore']);

    // Dashboard
    Route::get('/dashboard/getkpis', [DashboardController::class, 'getKpis']);
    Route::get('/dashboard/kpis', [DashboardController::class, 'kpis']);
    Route::get('/dashboard/sales-over-time', [DashboardController::class, 'salesOverTime']);
    Route::get('/dashboard/sales-by-category', [DashboardController::class, 'salesByCategory']);
    Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/dashboard/top-clients', [DashboardController::class, 'topClients']);
    Route::get('/dashboard/stock-alerts', [DashboardController::class, 'stockAlerts']);
});