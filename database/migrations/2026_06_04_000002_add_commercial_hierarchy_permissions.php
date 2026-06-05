<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'asignar urbanizaciones a grupos',
            'ver reporte comercial',
            'exportar reporte comercial',
            'gestionar supervisores comerciales',
            'gestionar supervisores de ventas',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super administrador', 'guard_name' => 'web']);
        $supervisorComercial = Role::firstOrCreate(['name' => 'supervisor comercial', 'guard_name' => 'web']);
        $supervisorVentas = Role::firstOrCreate(['name' => 'supervisor ventas', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permission::pluck('name')->all());
        $available = Permission::pluck('name');
        $supervisorComercial->givePermissionTo($available->intersect([
            'ver dashboard', 'ver lotes', 'ver clientes', 'ver ventas', 'ver reservas',
            'crear clientes', 'editar clientes', 'crear ventas', 'editar ventas', 'crear reservas',
            'ver reportes', 'ver reporte comercial', 'exportar reporte comercial', 'editar asesores',
        ])->all());
        $supervisorVentas->givePermissionTo($available->intersect([
            'ver dashboard', 'ver lotes', 'ver clientes', 'ver ventas', 'ver reservas', 'ver reservas equipo',
            'crear clientes', 'editar clientes', 'crear ventas', 'crear reservas', 'ver reportes', 'ver reporte comercial',
        ])->all());

        Role::where('name', 'administrador')->first()?->givePermissionTo($permissions);
        Role::where('name', 'gerente')->first()?->givePermissionTo(['ver reporte comercial', 'exportar reporte comercial']);
        Role::where('name', 'supervisor')->first()?->givePermissionTo(['ver reporte comercial']);
    }

    public function down(): void
    {
        Role::whereIn('name', ['super administrador', 'supervisor comercial', 'supervisor ventas'])->delete();
        Permission::whereIn('name', [
            'asignar urbanizaciones a grupos',
            'ver reporte comercial',
            'exportar reporte comercial',
            'gestionar supervisores comerciales',
            'gestionar supervisores de ventas',
        ])->delete();
    }
};
