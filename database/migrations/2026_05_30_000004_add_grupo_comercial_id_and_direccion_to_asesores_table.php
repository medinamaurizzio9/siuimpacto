<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesores', function (Blueprint $table) {
            if (! Schema::hasColumn('asesores', 'grupo_comercial_id')) {
                $table->foreignId('grupo_comercial_id')->nullable()->after('supervisor_id')->constrained('grupos_comerciales')->nullOnDelete();
            }
            if (! Schema::hasColumn('asesores', 'direccion')) {
                $table->string('direccion')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asesores', function (Blueprint $table) {
            if (Schema::hasColumn('asesores', 'grupo_comercial_id')) {
                $table->dropConstrainedForeignId('grupo_comercial_id');
            }
            if (Schema::hasColumn('asesores', 'direccion')) {
                $table->dropColumn('direccion');
            }
        });
    }
};
