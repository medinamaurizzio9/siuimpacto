<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            if (! Schema::hasColumn('lotes', 'precio_real_override_usd')) {
                $table->decimal('precio_real_override_usd', 12, 2)->nullable()->after('precio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            if (Schema::hasColumn('lotes', 'precio_real_override_usd')) {
                $table->dropColumn('precio_real_override_usd');
            }
        });
    }
};
