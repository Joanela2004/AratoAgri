<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_commandes', function (Blueprint $table) {
            $table->id('numDetailCommande');
            $table->foreignId('numCommande')
                  ->constrained('commandes','numCommande')
                  ->onDelete('cascade');
            $table->foreignId('numProduit')
                  ->constrained('produits','numProduit')
                  ->onDelete('restrict');
            $table->decimal('poids',10,2);
             $table->foreignId('numDecoupe')->nullable()->constrained('decoupes','numDecoupe')->nullOnDelete();
            $table->decimal('prixUnitaire',14,2);
            $table->decimal('sousTotal',14,2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_commandes');
    }
};
