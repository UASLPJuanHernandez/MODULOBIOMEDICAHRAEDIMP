<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MantenimientoRoleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Crear rol de Mantenimiento si no existe
        $mantenimientoRole = Role::firstOrCreate(['name' => 'Personal de Mantenimiento']);
        
        // Crear permisos específicos para mantenimiento
        $permissions = [
            'view_mantenimientos',
            'aceptar_mantenimientos', 
            'completar_mantenimientos',
            'rechazar_mantenimientos',
            'generar_vales_mantenimiento',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Asignar permisos al rol de mantenimiento
        $mantenimientoRole->syncPermissions($permissions);
        
        // Los administradores también pueden gestionar mantenimientos
        $adminRole = Role::where('name', 'Administrador')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
        
        echo "Roles y permisos de mantenimiento creados exitosamente.\n";
    }
}
