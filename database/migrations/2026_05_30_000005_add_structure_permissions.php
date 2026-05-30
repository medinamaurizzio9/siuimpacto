<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'crear supervisores',
            'editar supervisores',
            'desactivar supervisores',
            'crear grupos comerciales',
            'editar grupos comerciales',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::where('name', 'administrador')->first()?->givePermissionTo($permissions);
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'crear supervisores',
            'editar supervisores',
            'desactivar supervisores',
            'crear grupos comerciales',
            'editar grupos comerciales',
        ])->delete();
    }
};
