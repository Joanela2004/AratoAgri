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
    DecoupeController
};

// Auth
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout']);
Route::post('/change-password', [AuthController::class, 'changePassword']);

// Public
Route::get('produits', [ProduitController::class, 'index']);
Route::get('produits/{id}', [ProduitController::class, 'show']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{id}', [ArticleController::class, 'show']);
Route::get('categories', [CategorieController::class, 'index']);
Route::get('categories/{id}', [CategorieController::class, 'show']);
Route::get('promotions', [PromotionController::class, 'index']);
Route::get('promotions/{id}', [PromotionController::class, 'show']);
Route::get('lieux_livraison', [LieuLivraisonController::class, 'index']);
Route::get('/mode_paiements/actifs', [ModePaiementController::class, 'actifs']);

Route::get('mesCommandes', [CommandeController::class,'indexClient']);
Route::post('commandes', [CommandeController::class,'store']);
Route::get('mesCommandes/{id}', [CommandeController::class,'showClient']);
Route::put('mesCommandes/{id}', [CommandeController::class,'updateClient']);
Route::get('frais_livraisons', [FraisLivraisonController::class,'index']);
Route::get('mesLivraisons/{id}', [LivraisonController::class,'showClient']);

Route::middleware(['auth:sanctum', 'IsAdmin'])->group(function(){

    Route::get('clients/avec-commandes', [UtilisateurController::class, 'clientsAvecCommandes']);

    Route::post('send-email', [PromotionController::class, 'sendEmailToUsers']);

   
    Route::get('paiements', [PaiementController::class, 'index']);
    Route::post('paiements', [PaiementController::class, 'store']);
    Route::get('paiements/{id}', [PaiementController::class, 'show']);
    Route::put('paiements/{id}', [PaiementController::class, 'update']);
    Route::delete('paiements/{id}', [PaiementController::class, 'destroy']);

    Route::apiResource('decoupes', DecoupeController::class);

    Route::apiResource('commandes', CommandeController::class);
    Route::apiResource('livraisons', LivraisonController::class);
    Route::apiResource('frais_livraisons', FraisLivraisonController::class)->except(['index']);
    Route::apiResource('detail_commandes', DetailCommandeController::class);
    Route::apiResource('utilisateurs', UtilisateurController::class);
    Route::apiResource('mode_paiements', ModePaiementController::class);
    Route::apiResource('produits', ProduitController::class)->except(['index']);
     Route::apiResource('categories',CategorieController::class)->except(['index']);
    Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
    Route::apiResource('promotions', PromotionController::class)->except(['index', 'show']);
    Route::apiResource('lieux_livraison', LieuLivraisonController::class);
});
