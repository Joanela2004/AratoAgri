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
                  ->constrained('utilisateurs', 'numUtilisateur')
                  ->onDelete('cascade');

            $table->foreignId('numModePaiement')
                  ->constrained('mode_Paiements', 'numModePaiement')
                  ->onDelete('restrict');

            $table->foreignId('numLieu')
                  ->nullable()
                  ->constrained('lieux_livraison', 'numLieu')
                  ->onDelete('set null');

            $table->foreignId('numPromotion')
                  ->nullable()
                  ->constrained('promotions', 'numPromotion')
                  ->onDelete('set null');

            $table->string('codePromo')->nullable();
            $table->string('statut')->default('en attente');
            $table->decimal('sousTotal', 14, 2);
            $table->decimal('fraisLivraison', 14, 2)->default(0);
            $table->decimal('montantTotal', 14, 2);
            $table->boolean('payerLivraison')->default(false);
            $table->dateTime('dateCommande')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
