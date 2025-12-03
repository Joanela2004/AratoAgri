<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('frais_livraisons', function (Blueprint $table) {
        $table->softDeletes();
    });
}

public function down()
{
    Schema::table('frais_livraisons', function (Blueprint $table) {
        $table->dropSoftDeletes();
    });
}

};
