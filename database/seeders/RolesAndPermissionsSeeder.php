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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos para Usuarios
        Permission::firstOrCreate(['name' => 'crear usuarios']);
        Permission::firstOrCreate(['name' => 'leer usuarios']);
        Permission::firstOrCreate(['name' => 'actualizar usuarios']);
        Permission::firstOrCreate(['name' => 'eliminar usuarios']);

        // Permisos para Productos
        Permission::firstOrCreate(['name' => 'crear productos']);
        Permission::firstOrCreate(['name' => 'leer productos']);
        Permission::firstOrCreate(['name' => 'actualizar productos']);
        Permission::firstOrCreate(['name' => 'eliminar productos']);

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Rol: Dueños y guías estratégicos de la empresa (Acceso total)
        Role::firstOrCreate(['name' => 'Director de Operaciones']);
        Role::firstOrCreate(['name' => 'Director de Administración']);

        // Rol: Gestión administrativa, financiera y documental
        Role::firstOrCreate(['name' => 'Coordinador de Administración']);
        Role::firstOrCreate(['name' => 'Auxiliar de Administración']);

        // Rol: Gestión de almacén, logística e inventarios
        Role::firstOrCreate(['name' => 'Coordinador de Operaciones']);
        Role::firstOrCreate(['name' => 'Jefe de Operaciones']);
        Role::firstOrCreate(['name' => 'Auxiliar de Operaciones']);

        // Rol: Validación de instrumentación quirúrgica
        Role::firstOrCreate(['name' => 'Técnicos']);
    }
}
