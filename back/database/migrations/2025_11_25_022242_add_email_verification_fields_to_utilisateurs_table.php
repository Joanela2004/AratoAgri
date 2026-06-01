<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            // Ajouter la colonne de vérification d'email standard (timestamp)
            if (!Schema::hasColumn('utilisateurs', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            
            // Ajouter la colonne pour le token de vérification
            if (!Schema::hasColumn('utilisateurs', 'email_verification_token')) {
                $table->string('email_verification_token', 60)->nullable()->after('email_verified_at');
            }
            
            // Nettoyage de l'ancienne colonne booléenne si elle existait
            if (Schema::hasColumn('utilisateurs', 'email_verified')) {
                $table->dropColumn('email_verified');
            }
        });
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            if (Schema::hasColumn('utilisateurs', 'email_verification_token')) {
                $table->dropColumn('email_verification_token');
            }
            if (Schema::hasColumn('utilisateurs', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            // Ne pas recréer 'email_verified' dans le down
        });
    }
};