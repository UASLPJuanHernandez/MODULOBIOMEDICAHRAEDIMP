<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            'create_mantenimientos',
            'edit_mantenimientos',
            'aceptar_mantenimientos', 
            'completar_mantenimientos',
            'rechazar_mantenimientos',
            'generar_vales_mantenimiento',
            'view ordenes servicio',
            'create ordenes servicio',
            'edit ordenes servicio',
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
        
        // Crear usuario de mantenimiento por defecto
        $mantenimiento = User::firstOrCreate(
            ['email' => 'mantenimiento@inventario.hospital'],
            [
                'name' => 'Personal de Mantenimiento',
                'password' => Hash::make('mantenimiento123'),
            ]
        );
        
        // Asignar rol si no lo tiene
        if (!$mantenimiento->hasRole('Personal de Mantenimiento')) {
            $mantenimiento->assignRole('Personal de Mantenimiento');
        }
        
        echo "Roles y permisos de mantenimiento creados exitosamente.\n";
        echo "Usuario creado: mantenimiento@inventario.hospital (Password: mantenimiento123)\n";
    }
}
