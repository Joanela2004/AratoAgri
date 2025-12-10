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
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->boolean('statutPromotion')->default(true)->change();
        });
        DB::table('promotions')
            ->where('statutPromotion', '1')
            ->orWhere('statutPromotion', 1)
            ->update(['statutPromotion' => true]);

        DB::table('promotions')
            ->where('statutPromotion', '0')
            ->orWhere('statutPromotion', 0)
            ->orWhereNull('statutPromotion')
            ->update(['statutPromotion' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
