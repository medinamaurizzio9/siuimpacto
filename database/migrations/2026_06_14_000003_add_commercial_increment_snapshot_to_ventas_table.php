<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            if (! Schema::hasColumn('ventas', 'incremento_credito_tipo')) {
                $table->string('incremento_credito_tipo')->nullable()->after('precio_base_usd');
            }

            if (! Schema::hasColumn('ventas', 'incremento_credito_valor')) {
                $table->decimal('incremento_credito_valor', 12, 2)->nullable()->after('incremento_credito_tipo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            $table->dropColumn(['incremento_credito_tipo', 'incremento_credito_valor']);
        });
    }
};
