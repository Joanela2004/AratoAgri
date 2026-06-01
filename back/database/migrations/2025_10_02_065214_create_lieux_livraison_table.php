<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieux_livraison', function (Blueprint $table) {
            $table->id('numLieu');
            $table->string('nomLieu', 100)->unique();
            $table->decimal('fraisLieu', 10, 2)->default(0.00);
            $table->timestamps();
        });

       
    }

    public function down(): void
    {
       

        Schema::dropIfExists('lieux_livraison');
    }
};
