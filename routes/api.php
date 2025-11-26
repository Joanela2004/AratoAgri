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
    PanierController,
    DetailPanierController
};

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Verification d'email
Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

// Routes publiques - Catalogues et Infos
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
Route::get('lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('mode_paiements/actifs', [ModePaiementController::class, 'actifs']);
Route::get('frais_livraisons', [FraisLivraisonController::class, 'index']);

// Routes protégées - Client authentifié
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Utilisateur
    Route::post('/logout', [AuthController::class, 'logout']); // Déplacé ici pour être protégé
    Route::get('utilisateurs/{id}', [UtilisateurController::class, 'show']);
    Route::post('utilisateurs/{id}', [UtilisateurController::class, 'update']); 

    // Panier 
    // GET /panier : Afficher le panier de l'utilisateur connecté (index)
    Route::get('/panier', [PanierController::class, 'index']); // <<< AJOUTÉ/CORRIGÉ

    // POST /panier : Ajouter un produit au panier (store)
    Route::post('/panier', [PanierController::class, 'store']); 

    // DELETE /panier : Vider tout le panier (clear)
    Route::delete('/panier', [PanierController::class, 'clear']); // <<< AJOUTÉ/CORRIGÉ
    
    // Routes spécifiques aux détails du panier (nécessitent l'ID du détail)
    Route::put('panier/{id}', [PanierController::class, 'update']);
    Route::delete('/panier/{id}', [PanierController::class, 'destroy']);
    
    // La route show par ID de panier complet n'est généralement pas utilisée ici
    Route::get('/panier/{id}', [PanierController::class, 'show']);


    // Commandes & Paiement
    Route::post('/stripe/checkout', [StripeController::class, 'createCheckoutSession']);
    Route::get('mesCommandes', [CommandeController::class, 'indexClient']);
    Route::post('commandes', [CommandeController::class, 'store']);
    Route::get('mesCommandes/{id}', [CommandeController::class, 'showClient']); 
    Route::put('mesCommandes/{id}', [CommandeController::class, 'updateClient']);
    Route::delete('mesCommandes/{id}', [CommandeController::class, 'destroy']); 
    Route::post('/valider-code-promo', [PromotionController::class, 'validerCodePromo']);

    Route::get('commandes/{id}/livraisons', [LivraisonController::class, 'showByCommandeClient']);
});

// Routes protégées - Administration
Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {
    
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    Route::get('utilisateurs', [UtilisateurController::class, 'index']); 
    Route::post('send-promo-to-client', [PromotionController::class, 'sendEmailToUsers']);  
   
    Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('commandes', CommandeController::class)->except(['store']);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    
    Route::put('livraisons/{id}', [LivraisonController::class, 'update']);
    Route::get('livraisons/{id}', [LivraisonController::class, 'show']);
    Route::get('livraisons', [LivraisonController::class, 'index']);
    
    Route::apiResource('mode_paiements', ModePaiementController::class)->except(['actifs']);
    
    // Routes de catalogue Admin
    Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
    Route::apiResource('decoupes', DecoupeController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategorieController::class)->except(['index', 'show']);
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::apiResource('promotions', PromotionController::class)->except(['index', 'show']);
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
});