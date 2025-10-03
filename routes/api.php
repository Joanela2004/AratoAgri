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
    PanierController
};

Route::apiResource('utilisateurs', UtilisateurController::class);
Route::apiResource('categories', CategorieController::class);
Route::apiResource('mode_paiements', ModePaiementController::class);
Route::apiResource('promotions', PromotionController::class);
Route::apiResource('articles', ArticleController::class);