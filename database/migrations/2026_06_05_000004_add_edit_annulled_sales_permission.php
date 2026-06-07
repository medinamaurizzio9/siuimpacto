<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'editar ventas anuladas',
            'guard_name' => 'web',
        ]);

        Role::where('name', 'administrador')->first()?->givePermissionTo($permission);
    }

    public function down(): void
    {
        Permission::where('name', 'editar ventas anuladas')->delete();
    }
};
