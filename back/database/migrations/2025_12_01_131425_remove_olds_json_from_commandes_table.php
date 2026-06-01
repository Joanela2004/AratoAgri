<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('detail_commandes', function (Blueprint $table) {
        if (Schema::hasColumn('detail_commandes', 'produit_info')) {
            $table->dropColumn('produit_info');
        }
        if (Schema::hasColumn('commandes', 'livraison_info')) {
            $table->dropColumn('livraison_info');
        }
    });
}

public function down()
{
    Schema::table('detail_commandes', function (Blueprint $table) {
        $table->json('produit_info')->nullable();
        $table->json('livraison_info')->nullable();
    });
}

};
