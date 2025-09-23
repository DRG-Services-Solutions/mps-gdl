<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contraseña predeterminada para todos los usuarios, a ser reemplazada.
        $defaultPassword = Hash::make('password');

        // --- 1. Dirección y Admin ---
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@mps.com'],
            ['name' => 'Admin MPS', 'password' => $defaultPassword]
        );
        $adminUser->assignRole('admin');
        // Puesto: Dirección Operaciones
        $direccion1 = User::firstOrCreate(
            ['email' => 'direccion1@mps.com'],
            ['name' => 'Dirección Operaciones', 'password' => $defaultPassword]
        );
        $direccion1->assignRole('Director de Operaciones');

        // Puesto: Dirección Administrativa
        $direccion2 = User::firstOrCreate(
            ['email' => 'direccion2@mps.com'],
            ['name' => 'Dirección Administrativa', 'password' => $defaultPassword]
        );
        $direccion2->assignRole('Director de Administración');

        // Puesto: Usuario administrador predeterminado
        $direccion3 = User::firstOrCreate(
            ['email' => 'direccion3@mps.com'],
            ['name' => 'Dirección 3', 'password' => $defaultPassword]
        );
        $direccion3->assignRole('admin');


        // --- 2. Administración ---
        // Puesto: Coordinador de Administración
        $admin1 = User::firstOrCreate(
            ['email' => 'admin1@mps.com'],
            ['name' => 'Coordinador de Administración', 'password' => $defaultPassword]
        );
        $admin1->assignRole('Coordinador de Administración');

        // Puesto: Auxiliar Administración
        $auxAdmin1 = User::firstOrCreate(
            ['email' => 'auxadmin1@mps.com'],
            ['name' => 'Auxiliar Administración', 'password' => $defaultPassword]
        );
        $auxAdmin1->assignRole('Auxiliar de Administración');

        // Puesto: Auxiliar Administración (2)
        $auxAdmin2 = User::firstOrCreate(
            ['email' => 'auxadmin2@mps.com'],
            ['name' => 'Auxiliar Administración 2', 'password' => $defaultPassword]
        );
        $auxAdmin2->assignRole('Auxiliar de Administración');


        // --- 3. Técnicos ---
        // Puesto: Revisión Técnica
        $tecnicos1 = User::firstOrCreate(
            ['email' => 'tecnicos1@mps.com'],
            ['name' => 'Técnicos de Revisión', 'password' => $defaultPassword]
        );
        $tecnicos1->assignRole('Técnicos');


        // --- 4. Operaciones ---
        // Puesto: Coordinador de Operaciones
        $operaciones1 = User::firstOrCreate(
            ['email' => 'operaciones1@mps.com'],
            ['name' => 'Coordinador de Operaciones', 'password' => $defaultPassword]
        );
        $operaciones1->assignRole('Coordinador de Operaciones');

        // Puesto: Jefe de Operaciones
        $operaciones2 = User::firstOrCreate(
            ['email' => 'operaciones2@mps.com'],
            ['name' => 'Jefe de Operaciones', 'password' => $defaultPassword]
        );
        $operaciones2->assignRole('Jefe de Operaciones');

        // Puesto: Auxiliares de Operaciones
        $auxOperaciones1 = User::firstOrCreate(
            ['email' => 'auxoperaciones1@mps.com'],
            ['name' => 'Auxiliar de Operaciones 1', 'password' => $defaultPassword]
        );
        $auxOperaciones1->assignRole('Auxiliar de Operaciones');

        $auxOperaciones2 = User::firstOrCreate(
            ['email' => 'auxoperaciones2@mps.com'],
            ['name' => 'Auxiliar de Operaciones 2', 'password' => $defaultPassword]
        );
        $auxOperaciones2->assignRole('Auxiliar de Operaciones');

        $auxOperaciones3 = User::firstOrCreate(
            ['email' => 'auxoperaciones3@mps.com'],
            ['name' => 'Auxiliar de Operaciones 3', 'password' => $defaultPassword]
        );
        $auxOperaciones3->assignRole('Auxiliar de Operaciones');

        $auxOperaciones4 = User::firstOrCreate(
            ['email' => 'auxoperaciones4@mps.com'],
            ['name' => 'Auxiliar de Operaciones 4', 'password' => $defaultPassword]
        );
        $auxOperaciones4->assignRole('Auxiliar de Operaciones');
    }
}
