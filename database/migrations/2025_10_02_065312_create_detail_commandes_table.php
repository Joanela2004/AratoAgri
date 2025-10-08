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
        Schema::create('detail_commandes', function (Blueprint $table) {
            $table->id('numDetailCommande');
            $table->foreignId('numCommande')->constrained('commandes','numCommande')->onDelete('cascade');
            $table->foreignId('numProduit')->constrained('produits','numProduit')->onDelete('restrict');
            $table->decimal('poids',10,2)->default(0.25);
            $table->string('decoupe')->default('entière');
            $table->decimal('prixUnitaire',14,0);
            $table->decimal('sousTotal',14,0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_commandes');
    }
};
