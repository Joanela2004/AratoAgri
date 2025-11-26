<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('paniers', function (Blueprint $table) {
            $table->id('numPanier'); 
            $table->foreignId('numUtilisateur')
                  ->constrained('utilisateurs', 'numUtilisateur')
                  ->onDelete('cascade'); 
            $table->string('statut')->default('en_cours');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paniers');
    }
};
