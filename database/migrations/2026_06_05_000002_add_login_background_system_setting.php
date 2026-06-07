<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'login_background'],
            ['value' => '', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        DB::table('system_settings')->where('key', 'login_background')->delete();
    }
};
