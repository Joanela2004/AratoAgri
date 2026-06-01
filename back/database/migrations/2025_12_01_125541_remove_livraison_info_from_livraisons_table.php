<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            if (Schema::hasColumn('commandes', 'produit_info')) {
                $table->dropColumn('produit_info');
            }
        });

        Schema::table('livraisons', function (Blueprint $table) {
            if (Schema::hasColumn('livraisons', 'livraison_info')) {
                $table->dropColumn('livraison_info');
            }
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->json('produit_info')->nullable();
        });

        Schema::table('livraisons', function (Blueprint $table) {
            $table->json('livraison_info')->nullable();
        });
    }
};
