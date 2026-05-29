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
        Schema::create('manzanos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('urbanizacion_id')->constrained('urbanizaciones')->cascadeOnDelete();
            $table->string('codigo');
            $table->string('nombre')->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->unique(['urbanizacion_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manzanos');
    }
};
