<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = 
        [
            ['first_name' => 'Alberto', 'middle_name' => null, 'last_name' => 'Chavez', 'phone' => null, 'is_active' => true],
            ['first_name' => 'Alejandro', 'middle_name' => null, 'last_name' => 'Chavez', 'phone' => null, 'is_active' => true],
            ['first_name' => 'Alfonso', 'middle_name' => null, 'last_name' => 'Espinoza', 'phone' => null, 'is_active' => true],
            ['first_name' => 'Alberto', 'middle_name' => null, 'last_name' => 'Martinez', 'phone' => null, 'is_active' => false],
            ['first_name' => 'Angel', 'middle_name' => null, 'last_name' => 'Martinez', 'phone' => null, 'is_active' => true],
            ['first_name' => 'Alvaro', 'middle_name' => null, 'last_name' => 'Mercado', 'phone' => null, 'is_active' => true],
            ['first_name' => 'Arturo', 'middle_name' => null, 'last_name' => 'Mercado', 'phone' => null, 'is_active' => false],
            ['first_name' => 'Fernanda', 'middle_name' => null, 'last_name' => 'Espinoza', 'phone' => null, 'is_active' => true],
        ];

        foreach ($doctors as $doc)
        {
            Doctor::create($doc);
            $this->command->info("✅ Doctor Agregado :  {$doc['first_name']} - {$doc['last_name']}");
        }
    }
}
