<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_utilisateur', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('numPromotion');
            $table->unsignedBigInteger('numUtilisateur');
            $table->string('code_envoye')->nullable();
            $table->dateTime('date_expiration')->nullable();
            $table->string('statut', 20)->default('valide');
            $table->timestamps();

            $table->foreign('numPromotion')
                  ->references('numPromotion')
                  ->on('promotions')
                  ->onDelete('cascade');

            $table->foreign('numUtilisateur')
                  ->references('numUtilisateur')
                  ->on('utilisateurs')
                  ->onDelete('cascade');

            $table->unique(['numPromotion', 'numUtilisateur']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_utilisateur');
    }
};