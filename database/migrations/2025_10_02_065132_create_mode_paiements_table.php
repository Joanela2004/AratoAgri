<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modePaiements', function (Blueprint $table) {
            $table->id('numModePaiement');
            $table->string('nomModePaiement', 100);
            $table->boolean('actif')->default(true);
            $table->json('config')->nullable(); 
            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modePaiements');
    }
};
