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
    PanierController,
    DetailPanierController,
    DetailCommandeController,
    FraisLivraisonController,
    AuthController
};

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::get('produits', [ProduitController::class, 'index']);
Route::get('produits/{id}', [ProduitController::class, 'show']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{id}', [ArticleController::class, 'show']);
Route::get('categories', [CategorieController::class, 'index']);
Route::get('categories/{id}', [CategorieController::class, 'show']);
Route::get('promotions', [PromotionController::class, 'index']);
Route::get('promotions/{id}', [PromotionController::class, 'show']);

Route::get('/test', function () {
    return response()->json(['message' => 'API working!']);
});
 
Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::post('/admin/change-password', [AuthController::class, 'changePassword']);
    
    Route::apiResource('paniers', PanierController::class);
    Route::apiResource('detail_paniers', DetailPanierController::class);
    
    Route::get('mesCommandes', [CommandeController::class,'indexClient']);
    Route::post('commandes', [CommandeController::class,'store']);
    Route::get('mesCommandes/{id}', [CommandeController::class,'showClient']);
    Route::put('mesCommandes/{id}', [CommandeController::class,'updateClient']);
    
    Route::get('mesLivraisons', [LivraisonController::class,'indexClient']);
    Route::get('mesLivraisons/{id}', [LivraisonController::class,'showClient']);

    Route::middleware('isAdmin')->group(function (){
        Route::post('categories', [CategorieController::class, 'store']);
        Route::put('categories/{id}', [CategorieController::class, 'update']);
        Route::delete('categories/{id}', [CategorieController::class, 'destroy']);
        
        Route::apiResource('commandes', CommandeController::class);
        Route::apiResource('livraisons', LivraisonController::class);
        Route::apiResource('frais_livraisons', FraisLivraisonController::class);
        Route::apiResource('detail_commandes', DetailCommandeController::class);
        Route::apiResource('paiements', PaiementController::class);
        Route::apiResource('utilisateurs', UtilisateurController::class);
        Route::apiResource('mode_paiements', ModePaiementController::class);
        Route::apiResource('produits', ProduitController::class)->except(['index', 'show']);
        Route::apiResource('articles', ArticleController::class)->except(['index', 'show']);
        Route::apiResource('promotions', PromotionController::class)->except(['index', 'show']);
    });
});