<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Api\{
    ProduitController,
    CategorieController,
    ArticleController,
    PromotionController,
    UtilisateurController,
    ModePaiementController,
    CommandeController,
    PaiementController,
    LivraisonController,
    PanierController,
    DetailPanierController,
    DetailCommandeController,
  
};

Route::apiResource('utilisateurs', UtilisateurController::class);
Route::apiResource('categories', CategorieController::class);
Route::apiResource('mode_paiements', ModePaiementController::class);
Route::apiResource('promotions', PromotionController::class);
Route::apiResource('articles', ArticleController::class);
Route::apiResource('produits', ProduitController::class);
Route::apiResource('detail_paniers', DetailPanierController::class);
Route::apiResource('commandes',CommandeController::class);
Route::apiResource('detail_commandes',DetailCommandeController::class);
Route::apiResource('paniers',PanierController::class);
Route::apiResource('paiements',PaiementController::class);
Route::apiResource('livraisons',LivraisonController::class);
