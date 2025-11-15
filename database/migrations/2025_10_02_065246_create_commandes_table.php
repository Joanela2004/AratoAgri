<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id('numCommande');
            $table->foreignId('numUtilisateur')
                  ->constrained('utilisateurs','numUtilisateur')
                  ->onDelete('cascade');
            $table->dateTime('dateCommande')->nullable();
            $table->enum('statut',['en cours','récu'])->default('en cours');
            $table->decimal('montantTotal',14,2)->default(0.00);
            $table->foreignId('numModePaiement')
                  ->constrained('mode_paiements','numModePaiement')
                  ->onDelete('restrict');
            $table->string('adresseDeLivraison',255);
            $table->boolean('payerLivraison')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
