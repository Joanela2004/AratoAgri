<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Promotions
        Schema::table('promotions', function (Blueprint $table) {
            $table->decimal('valeur', 10, 2)->change();
            $table->decimal('montantMinimum', 12, 2)->default(0)->change();
        });

        // Produits
        Schema::table('produits', function (Blueprint $table) {
            $table->decimal('prix', 12, 2)->change(); // ou le nom de ta colonne prix
        });

        // Commandes
        Schema::table('commandes', function (Blueprint $table) {
            $table->decimal('sousTotal', 14, 2)->change();
            $table->decimal('fraisLivraison', 10, 2)->default(0)->change();
            $table->decimal('montantTotal', 14, 2)->change();
        });

        // DetailCommande
        Schema::table('detail_commandes', function (Blueprint $table) {
            $table->decimal('prixUnitaire', 12, 2)->change();
            $table->decimal('sousTotal', 14, 2)->change();
        });

        // Paiements
        Schema::table('paiements', function (Blueprint $table) {
            $table->decimal('montantApayer', 14, 2)->change();
        });

        // Lieux livraison (si tu as des frais variables)
        Schema::table('lieux_livraison', function (Blueprint $table) {
            $table->decimal('fraisLieu', 10, 2)->default(0)->change();
        });
    }

    public function down()
    {
        // Tu peux laisser vide ou remettre les anciennes tailles si tu veux
    }
};