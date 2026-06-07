<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'ver recibo reserva',
            'descargar recibo reserva',
            'imprimir recibo reserva',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::where('name', 'administrador')->first()?->givePermissionTo($permissions);
        Role::where('name', 'supervisor')->first()?->givePermissionTo($permissions);
        Role::where('name', 'vendedor')->first()?->givePermissionTo($permissions);
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'ver recibo reserva',
            'descargar recibo reserva',
            'imprimir recibo reserva',
        ])->delete();
    }
};
