<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mode_paiements', function (Blueprint $table) {
            $table->id('numModePaiement');
            $table->string('nomModePaiement', 100);   // Carte, Mobile, PayPal, Virement
            $table->decimal('solde', 10, 2)->default(0.00);

            /** Carte bancaire */
            $table->string('numero_carte', 50)->nullable();
            $table->string('nom_carte', 150)->nullable();
            $table->string('expiration_carte', 10)->nullable(); // mm/yy
            $table->string('cvv', 10)->nullable();

            /** Virement bancaire */
            $table->string('nom_banque', 150)->nullable();
            $table->string('numero_compte', 150)->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('swift', 20)->nullable();

            /** Mobile Money */
            $table->string('operateur_mobile', 50)->nullable(); // MVola, Orange, Airtel
            $table->string('numero_mobile', 30)->nullable();
            $table->string('nom_mobile', 150)->nullable(); // Nom du titulaire

            /** PayPal */
            $table->string('paypal_email', 150)->nullable();
            $table->string('paypal_id', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mode_paiements');
    }
};
