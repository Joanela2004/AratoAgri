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
};
// ==================== ROUTES PUBLIQUES (SANS AUTH) ====================
Route::get('/decoupes', [DecoupeController::class, 'index']);
Route::get('/frais_livraisons', [FraisLivraisonController::class, 'index']);
Route::get('/lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('/modes_paiement/actifs', [ModePaiementController::class, 'actifs']); // CRUCIAL
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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/promotions/valider', [PromotionController::class, 'valider']);


     Route::get('utilisateurs/{id}', [UtilisateurController::class, 'show']);
     Route::put('utilisateurs/{id}', [UtilisateurController::class, 'update']);
Route::put('utilisateurs/{id}', [UtilisateurController::class, 'update']);

    Route::get('mesCommandes/{numCommande}/livraison', [LivraisonController::class, 'showByCommandeClient']);

  
    // Commandes
   Route::get('/mesCommandes', [CommandeController::class, 'indexClient']);
   Route::get('/mesCommandes/{id}', [CommandeController::class, 'showClient']);
    Route::post('/commandes', [CommandeController::class, 'store']); 

});
   
    Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {
  
   
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::put('/commandes/{numCommande}', [CommandeController::class, 'update']); 
    Route::get('utilisateurs', [UtilisateurController::class, 'index']); 
    Route::post('/send-promo-to-client', [PromotionController::class, 'sendPromoToClient']);
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('commandes', CommandeController::class)->except(['store','showClient','indexClient']);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    Route::get('/promotions/deja-envoye/{numPromotion}/{numUtilisateur}',[PromotionController::class, 'checkIfSent']);

    Route::put('livraisons/{id}', [LivraisonController::class, 'update']);
    Route::get('livraisons/{id}', [LivraisonController::class, 'show']);
    Route::get('livraisons', [LivraisonController::class, 'index']);
    
    Route::apiResource('mode_paiements', ModePaiementController::class)->except(['actifs']);
    
    // Routes de catalogue Admin
    Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
    Route::apiResource('decoupes', DecoupeController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategorieController::class)->except(['index', 'show']);
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::apiResource('promotions', PromotionController::class);
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
});
