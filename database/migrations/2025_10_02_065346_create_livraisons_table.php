<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id('numLivraison');
            $table->foreignId('numCommande')
                  ->constrained('commandes','numCommande')
                  ->onDelete('cascade');
            $table->string('lieuLivraison',100)->nullable();
            $table->string('transporteur')->default('à déterminer');
            $table->string('referenceColis')->nullable();
            $table->decimal('fraisLivraison',14,2)->default(0.00);
            $table->string('contactTransporteur')->nullable();
            $table->dateTime('dateExpedition')->nullable();
            $table->dateTime('dateLivraison')->nullable();
            $table->enum('statutLivraison',['en cours','en préparation','livré'])->default('en préparation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};
