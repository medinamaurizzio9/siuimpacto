<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urbanizaciones', function (Blueprint $table) {
            $table->string('propietario')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('urbanizaciones', function (Blueprint $table) {
            $table->dropColumn('propietario');
        });
    }
};
