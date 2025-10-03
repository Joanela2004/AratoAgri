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
            $table->foreignId('numPanier')->constrained('paniers','numPanier')->onDelete('cascade');
             $table->foreignId('numProduit')->constrained('produits','numProduit')->onDelete('restrict');
            $table->unsignedInteger('quantite')->default(1);
            $table->unique(['numPanier','numProduit']);
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
