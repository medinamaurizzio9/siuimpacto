<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'ver reporte reservas',
            'exportar reporte reservas',
            'ver reporte mejor vendedor',
            'exportar reporte mejor vendedor',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::where('name', 'administrador')->first()?->givePermissionTo($permissions);
        Role::where('name', 'gerente')->first()?->givePermissionTo($permissions);
        Role::where('name', 'supervisor')->first()?->givePermissionTo(['ver reportes', 'ver reporte reservas', 'exportar reporte reservas']);
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'ver reporte reservas',
            'exportar reporte reservas',
            'ver reporte mejor vendedor',
            'exportar reporte mejor vendedor',
        ])->delete();
    }
};
