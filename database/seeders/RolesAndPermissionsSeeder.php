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

        //Permisos para Usuarios
        Permission::create(['name' => 'crear usuarios']);
        Permission::create(['name' => 'leer usuarios']);
        Permission::create(['name' => 'actualizar usuarios']);
        Permission::create(['name' => 'eliminar usuarios']);

        //Permisos para Productos
        Permission::create(['name' => 'crear productos']);
        Permission::create(['name' => 'leer productos']);
        Permission::create(['name' => 'actualizar productos']);
        Permission::create(['name' => 'eliminar productos']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $userRole = Role::create(['name' => 'usuario']);
        $userRole->givePermissionTo([
            'leer productos',
        ]);

        $adminUser = User::create([
            'name' => 'Admin MPS',
            'email' => 'admin@mps.com',
            'password' => Hash::make('password') 
        ]);
        $adminUser->assignRole($adminRole);

        $exampleUser = User::create([
            'name' => 'Example User',
            'email' => 'user@example.com',
            'password' => Hash::make('password')
        ]);
        $exampleUser->assignRole($userRole);
    }
}
