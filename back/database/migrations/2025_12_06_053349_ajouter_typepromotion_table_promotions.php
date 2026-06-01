<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->enum('typePromotion', ['Pourcentage', 'Montant fixe'])
                  ->default('Pourcentage')
                  ->after('valeur');
        });

        \DB::table('promotions')
            ->whereNull('typePromotion')
            ->orWhere('typePromotion', '')
            ->update(['typePromotion' => 'Pourcentage']);
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('typePromotion');
        });
    }
};