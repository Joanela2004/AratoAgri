<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_paniers', function (Blueprint $table) {
           $table->id('numDetailPanier'); 
            $table->foreignId('numPanier')->constrained('paniers', 'numPanier')->onDelete('cascade');
            $table->foreignId('numProduit')->constrained('produits', 'numProduit')->onDelete('restrict');
            $table->decimal('poids', 10, 2)->default(1);
            $table->decimal('prixUnitaire', 14, 2);
            $table->decimal('sousTotal', 14, 2);
            $table->foreignId('numDecoupe')->nullable()->constrained('decoupes','numDecoupe')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_paniers');
    }
};
