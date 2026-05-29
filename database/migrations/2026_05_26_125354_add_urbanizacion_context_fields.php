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
        Schema::table('urbanizaciones', function (Blueprint $table) {
            $table->boolean('mostrar_precio_publico')->default(true)->after('estado');
        });

        Schema::create('urbanizacion_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('urbanizacion_id')->constrained('urbanizaciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['urbanizacion_id', 'user_id']);
            $table->index(['user_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urbanizacion_user');

        Schema::table('urbanizaciones', function (Blueprint $table) {
            $table->dropColumn('mostrar_precio_publico');
        });
    }
};
