<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id('numPaiement');
            $table->foreignId('numCommande')
                  ->constrained('commandes','numCommande')
                  ->onDelete('cascade');
            $table->foreignId('numModePaiement')
                  ->constrained('mode_paiements','numModePaiement')
                  ->onDelete('restrict');
            $table->decimal('montantApayer',14,2);
            $table->enum('statut',['en attente','effectué','échoué'])->default('en attente');
            $table->dateTime('datePaiement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
