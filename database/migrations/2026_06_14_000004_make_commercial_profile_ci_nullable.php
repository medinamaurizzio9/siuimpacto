<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE asesores MODIFY ci VARCHAR(255) NULL');
        DB::statement('ALTER TABLE supervisor_profiles MODIFY ci VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE asesores MODIFY ci VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE supervisor_profiles MODIFY ci VARCHAR(255) NOT NULL');
    }
};
