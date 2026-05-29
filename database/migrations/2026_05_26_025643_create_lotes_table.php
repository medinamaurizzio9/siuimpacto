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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manzano_id')->constrained('manzanos')->cascadeOnDelete();
            $table->string('codigo');
            $table->decimal('superficie', 10, 2)->default(0);
            $table->decimal('precio', 12, 2)->default(0);
            $table->string('estado')->default('disponible');
            $table->unsignedInteger('fila')->default(1);
            $table->unsignedInteger('columna')->default(1);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['manzano_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
