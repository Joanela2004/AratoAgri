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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id('numPromotion');
            $table->string('nomPromotion',100);
            $table->decimal('valeur',5,2);
            $table->dateTime('dateDebut')->nullable();
            $table->dateTime('dateFin')->nullable();
            $table->string('codePromo',100)->unique()->nullable();
            $table->string('statutPromotion')->default(true);
            $table->decimal('montantMinimum', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
