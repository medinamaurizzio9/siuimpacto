<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urbanizacion_referencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('urbanizacion_id')->constrained('urbanizaciones')->cascadeOnDelete();
            $table->string('nombre');
            $table->decimal('latitud', 12, 8);
            $table->decimal('longitud', 12, 8);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['urbanizacion_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urbanizacion_referencias');
    }
};
