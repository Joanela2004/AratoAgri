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
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id('numLivraison');
            $table->foreignId('numCommande')->constrained('commandes','numCommande')->onDelete('cascade');
            $table->string('modeLivraison',100);
            $table->string('transporteur',100);
            $table->datetime('dateExpedition')->nullable();
            $table->datetime('datelivraison')->nullable();
            $table->enum('statutLivraison',['en cours','en préparation','livré(e)s','annulé'])->default('en préparation');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};
