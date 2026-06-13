<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urbanizaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('urbanizaciones', 'plano_archivo_original')) {
                $table->string('plano_archivo_original')->nullable()->after('plano_imagen');
            }
        });
    }

    public function down(): void
    {
        Schema::table('urbanizaciones', function (Blueprint $table) {
            if (Schema::hasColumn('urbanizaciones', 'plano_archivo_original')) {
                $table->dropColumn('plano_archivo_original');
            }
        });
    }
};
