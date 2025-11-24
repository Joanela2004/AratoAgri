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
    LieuLivraisonController,
    DecoupeController,
};

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

// Routes publiques - Catalogues
Route::get('produits', [ProduitController::class, 'index']);
Route::get('produits/{id}', [ProduitController::class, 'show']);
Route::get('decoupes', [DecoupeController::class, 'index']);
Route::get('decoupes/{id}', [DecoupeController::class, 'show']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{id}', [ArticleController::class, 'show']);
Route::get('categories', [CategorieController::class, 'index']);
Route::get('categories/{id}', [CategorieController::class, 'show']);
Route::get('promotions', [PromotionController::class, 'index']);
Route::get('promotions/{id}', [PromotionController::class, 'show']);

// Routes publiques - Utilitaires
Route::get('lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('mode_paiements/actifs', [ModePaiementController::class, 'actifs']);
Route::get('frais_livraisons', [FraisLivraisonController::class, 'index']);

// Routes protégées - Client authentifié
Route::middleware('auth:sanctum')->group(function () {
    
    // Profil utilisateur
    // La route POST pour l'update est essentielle pour le client
    Route::get('utilisateurs/{id}', [UtilisateurController::class, 'show']);
 Route::post('utilisateurs/{id}', [UtilisateurController::class, 'update']); 

    // Commandes du client
    Route::get('mesCommandes', [CommandeController::class, 'indexClient']);
    Route::post('commandes', [CommandeController::class, 'store']);
    Route::get('mesCommandes/{id}', [CommandeController::class, 'showClient']); 
    Route::put('mesCommandes/{id}', [CommandeController::class, 'updateClient']);
    Route::delete('mesCommandes/{id}', [CommandeController::class, 'destroy']); 
    
    // Livraisons du client
    Route::get('commandes/{id}/livraisons', [LivraisonController::class, 'showByCommandeClient']);
});

// Routes protégées - Admin uniquement
Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {
    
    // Changement de mot de passe
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Clients et Utilisateurs (Liste complète pour l'Admin)
    // Renommé en /admin/utilisateurs pour éviter le conflit avec la route client show/update
    Route::get('admin/utilisateurs', [UtilisateurController::class, 'index']); 
    Route::get('clients/avec-commandes', [UtilisateurController::class, 'clientsAvecCommandes']);
    
    // Emailing
    Route::post('send-promo-to-client', [PromotionController::class, 'sendEmailToUsers']);  
    // CRUD Admin standard (apiResource)
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('commandes', CommandeController::class)->except(['store']);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    
    // CRUD Admin manuel
    Route::put('livraisons/{id}', [LivraisonController::class, 'update']);
    Route::get('livraisons/{id}', [LivraisonController::class, 'show']);
    Route::get('livraisons', [LivraisonController::class, 'index']);
    
    // Routes spécifiques Utilisateur/ModePaiement
    // L'Admin ne doit pas pouvoir utiliser la route SHOW/UPDATE réservée au client
    Route::apiResource('utilisateurs', UtilisateurController::class)->except(['show', 'update']); 
    Route::apiResource('mode_paiements', ModePaiementController::class)->except(['actifs']);
    
    // Routes de catalogue Admin
    Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
    Route::apiResource('decoupes', DecoupeController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategorieController::class)->except(['index', 'show']);
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::apiResource('promotions', PromotionController::class)->except(['index', 'show']);
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
});