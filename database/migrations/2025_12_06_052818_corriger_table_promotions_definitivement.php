<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('promotions', function (Blueprint $table) {
            // Corriger la colonne valeur (doit supporter gros montants)
            $table->decimal('valeur', 12, 2)->default(0)->change();

            if (Schema::hasColumn('promotions', 'typePromotion')) {
                $table->dropColumn('typePromotion');
            }
          

            if (!Schema::hasColumn('promotions', 'automatique')) {
                $table->boolean('automatique')->default(false)->after('typePromotion');
            } else {
                $table->boolean('automatique')->default(false)->change();
            }

            if (!Schema::hasColumn('promotions', 'montantMinimum')) {
                $table->decimal('montantMinimum', 12, 2)->default(0)->after('automatique');
            } else {
                $table->decimal('montantMinimum', 12, 2)->default(0)->change();
            }

            if (Schema::hasColumn('promotions', 'statutPromotion')) {
                $table->boolean('statutPromotion')->default(true)->change();
            } else {
                $table->boolean('statutPromotion')->default(true)->after('dateFin');
            }

            if (!Schema::hasColumn('promotions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

       
    }

    public function down(): void
    {
       
    }
};