<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            
            if (!Schema::hasColumn('promotions', 'typePromotion')) {
                $table->enum('typePromotion', ['Montant', 'Pourcentage'])
                      ->default('Pourcentage')
                      ->after('valeur');
            }

            if (Schema::hasColumn('promotions', 'valeur')) {
                $table->decimal('valeur', 10, 2)->change();
            }

            if (Schema::hasColumn('promotions', 'statutPromotion')) {
                $table->boolean('statutPromotion')->default(true)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
          
            if (Schema::hasColumn('promotions', 'typePromotion')) {
                $table->dropColumn('typePromotion');
            }
        });
    }
};
