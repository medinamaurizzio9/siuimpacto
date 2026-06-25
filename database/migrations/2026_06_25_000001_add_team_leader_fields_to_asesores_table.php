<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asesores', function (Blueprint $table): void {
            if (! Schema::hasColumn('asesores', 'is_team_leader')) {
                $table->boolean('is_team_leader')->default(false)->after('activo');
            }

            if (! Schema::hasColumn('asesores', 'team_leader_role_assigned')) {
                $table->boolean('team_leader_role_assigned')->default(false)->after('is_team_leader');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asesores', function (Blueprint $table): void {
            if (Schema::hasColumn('asesores', 'team_leader_role_assigned')) {
                $table->dropColumn('team_leader_role_assigned');
            }

            if (Schema::hasColumn('asesores', 'is_team_leader')) {
                $table->dropColumn('is_team_leader');
            }
        });
    }
};
