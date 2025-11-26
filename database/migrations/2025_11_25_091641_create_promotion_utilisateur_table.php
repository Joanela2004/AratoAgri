<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('promotion_utilisateur', function (Blueprint $table) {
           $table->id('numPromotion_Utilisateur');
            $table->unsignedBigInteger('numPromotion');
            $table->unsignedBigInteger('numUtilisateur');
            $table->string('statut')->default('valide'); // valide / utilisé / expiré
            $table->dateTime('dateExpiration')->nullable();
            $table->timestamps();

            $table->foreign('numPromotion')->references('numPromotion')->on('promotions')->onDelete('cascade');
            $table->foreign('numUtilisateur')->references('numUtilisateur')->on('utilisateurs')->onDelete('cascade');

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
