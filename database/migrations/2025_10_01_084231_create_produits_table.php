<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id('numProduit');
            $table->string('nomProduit',100);
            $table->decimal('prix',10,2);
            $table->integer('quantiteStock')->default(0);
            $table->decimal('poids',10,2);
            $table->string('image',255)->nullable(false);
            $table->foreignId('numCategorie')->constrained('categories','numCategorie')->onDelete('restrict');
            $table->unsignedBigInteger('numPromotion')->nullable();
            $table->foreign('numPromotion')->references('numPromotion')->on('promotions')->nullOnDelete();
            $table->timestamps();
        });
    }
        
    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};