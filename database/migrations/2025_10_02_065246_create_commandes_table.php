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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id('numCommande');
            $table->foreignId('numUtilisateur')->constrained('utilisateurs','numUtilisateur')->onDelete('cascade');
            $table->foreignId('numModePaiement')->constrained('mode_paiements','numModePaiement')->onDelete('restrict');
            $table->dateTime('dateCommande')->useCurrent();
            $table->enum('statut',['en attente','payée','expédiée','terminée','annulée'])->default('en attente');
            $table->decimal('montantTotal',14,2)->default(0.00);
            $table->string('adresseDeLivraison',255);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
