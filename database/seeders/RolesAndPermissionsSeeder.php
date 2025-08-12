<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            // Mobiliario
            'view mobiliario',
            'create mobiliario',
            'edit mobiliario',
            'delete mobiliario',
            'generate qr mobiliario',
            
            // Movimientos
            'view movimientos',
            'create movimientos',
            'edit movimientos',
            'delete movimientos',
            
            // Vales
            'view vales',
            'create vales',
            'generate vales',
            
            // Órdenes de servicio
            'view ordenes servicio',
            'create ordenes servicio',
            'edit ordenes servicio',
            'delete ordenes servicio',
            'generate ordenes servicio',
            
            // Reportes
            'view reportes',
            'generate reportes',
            'export reportes',
            
            // Configuración
            'view configuracion',
            'edit configuracion',
            
            // Usuarios
            'view usuarios',
            'create usuarios',
            'edit usuarios',
            'delete usuarios',
            
            // Dashboard
            'view dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // Administrador (Jefe de Activo Fijo)
        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo(Permission::all());

        // Personal de Apoyo
        $staffRole = Role::create(['name' => 'Personal de Apoyo']);
        $staffRole->givePermissionTo([
            'view mobiliario',
            'create mobiliario',
            'edit mobiliario',
            'generate qr mobiliario',
            'view movimientos',
            'create movimientos',
            'edit movimientos',
            'view vales',
            'create vales',
            'generate vales',
            'view ordenes servicio',
            'create ordenes servicio',
            'edit ordenes servicio',
            'generate ordenes servicio',
            'view reportes',
            'generate reportes',
            'export reportes',
            'view dashboard',
        ]);

        // Jefe de Área
        $areaRole = Role::create(['name' => 'Jefe de Área']);
        $areaRole->givePermissionTo([
            'view mobiliario',
            'view movimientos',
            'view vales',
            'view ordenes servicio',
            'view reportes',
            'view dashboard',
        ]);

        // Crear usuario administrador por defecto
        $admin = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@inventario.hospital',
            'password' => Hash::make('admin123'),
        ]);
        $admin->assignRole('Administrador');

        // Crear usuario de apoyo por defecto
        $staff = User::create([
            'name' => 'Personal de Apoyo',
            'email' => 'apoyo@inventario.hospital',
            'password' => Hash::make('apoyo123'),
        ]);
        $staff->assignRole('Personal de Apoyo');

        // Crear usuario jefe de área por defecto
        $area = User::create([
            'name' => 'Jefe de Área',
            'email' => 'jefe@inventario.hospital',
            'password' => Hash::make('jefe123'),
        ]);
        $area->assignRole('Jefe de Área');
    }
}
