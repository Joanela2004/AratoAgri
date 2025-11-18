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
    {      Schema::create('frais_livraisons', function (Blueprint $table) {
            $table->id('numFrais');
            $table->decimal('poidsMin', 8, 2);
            $table->decimal('poidsMax', 8, 2);
            $table->decimal('frais', 10, 2);
            $table->timestamps();
           
        });

    
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_livraisons');
    }
};
