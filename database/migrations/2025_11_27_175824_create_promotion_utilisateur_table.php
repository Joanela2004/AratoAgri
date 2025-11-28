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
        Schema::create('promotion_utilisateur', function (Blueprint $table) {
          $table->id();
            $table->unsignedBigInteger('numPromotion');
            $table->unsignedBigInteger('numUtilisateur');
            $table->string('code_envoye')->nullable(); // le code exact envoyé (ex: VIP2025)
            $table->date('date_expiration')->nullable();
            $table->enum('statut', ['envoye', 'utilise', 'expire'])->default('envoye');
            $table->timestamps();

            // Clés étrangères
            $table->foreign('numPromotion')->references('numPromotion')->on('promotions')->onDelete('cascade');
            $table->foreign('numUtilisateur')->references('numUtilisateur')->on('utilisateurs')->onDelete('cascade');
            $table->unique(['numPromotion', 'numUtilisateur']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_utilisateur');
    }
};
