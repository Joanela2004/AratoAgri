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


Route::post('/register', [AuthController::class,'register']);
Route::post('/login',    [AuthController::class,'login']);
Route::post('/logout',   [AuthController::class,'logout']);


Route::get('produits',    [ProduitController::class, 'index']);
Route::get('produits/{id}', [ProduitController::class, 'show']);
Route::get('decoupes', [DecoupeController::class, 'index']);
Route::get('decoupes/{id}', [DecoupeController::class, 'show']);

Route::get('articles',     [ArticleController::class, 'index']);
Route::get('articles/{id}', [ArticleController::class, 'show']);

Route::get('categories',   [CategorieController::class, 'index']);
Route::get('categories/{id}', [CategorieController::class, 'show']);

Route::get('promotions',     [PromotionController::class, 'index']);
Route::get('promotions/{id}', [PromotionController::class, 'show']);

Route::get('lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('mode_paiements/actifs', [ModePaiementController::class, 'actifs']);
Route::get('frais_livraisons', [FraisLivraisonController::class,'index']);


Route::middleware('auth:sanctum')->group(function () {

    
    Route::get('mesCommandes',         [CommandeController::class,'indexClient']);
    Route::post('commandes',           [CommandeController::class,'store']);
    Route::get('mesCommandes/{id}',    [CommandeController::class,'showClient']); 
    Route::put('mesCommandes/{id}',    [CommandeController::class,'updateClient']);
    Route::delete('mesCommandes/{id}', [CommandeController::class,'destroy']); 
  
    // Livraisons
    Route::get('mesLivraisons/{id}', [LivraisonController::class,'showClient']);

    });



Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Clients ayant passé commande
    Route::get('clients/avec-commandes', [UtilisateurController::class, 'clientsAvecCommandes']);

    // Emailing
    Route::post('send-email', [PromotionController::class, 'sendEmailToUsers']);

    // Paiements
    Route::apiResource('paiements', PaiementController::class);

  
    // Commandes 
     Route::get('commandes',[CommandeController::class,'index']); 
  
     Route::get('commandes/{id}',[CommandeController::class,'show']); 
     Route::put('commandes/{id}', [CommandeController::class,'update']);

    // Livraisons 
    Route::apiResource('livraisons', LivraisonController::class);

    // Frais livraison 
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);

    // Utilisateurs
    Route::apiResource('utilisateurs', UtilisateurController::class);

    // Modes de paiement
    Route::apiResource('mode_paiements', ModePaiementController::class)->except(['actifs']);

    // Produits
    Route::apiResource('produits', ProduitController::class)->except(['index','show']);
    Route::apiResource('decoupes', DecoupeController::class)->except(['index','show']);
   
    // Catégories
    Route::apiResource('categories', CategorieController::class)->except(['index']);

    // Articles
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);

    // Promotions
    Route::apiResource('promotions', PromotionController::class)->except(['index', 'show']);

    // Lieux de livraison
    Route::apiResource('lieux_livraison', LieuLivraisonController::class)->except(['index']);
});
