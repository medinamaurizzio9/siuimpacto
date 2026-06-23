<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('urbanizacion_referencias', function (Blueprint $table): void {
            $table->string('tipo_referencia', 40)->default('otro')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('urbanizacion_referencias', function (Blueprint $table): void {
            $table->dropColumn('tipo_referencia');
        });
    }
};
