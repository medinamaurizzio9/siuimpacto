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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservas')->nullOnDelete();
            $table->foreignId('installment_id')->nullable()->constrained('cuotas')->nullOnDelete();
            $table->string('tipo');
            $table->string('concepto');
            $table->string('metodo_pago')->default('efectivo');
            $table->decimal('monto', 12, 2);
            $table->date('fecha');
            $table->string('referencia')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('confirmado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
