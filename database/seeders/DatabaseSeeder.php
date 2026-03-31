<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
                    RolesAndPermissionsSeeder::class,
                    ProductCategorySeeder::class,
                    SupplierSeeder::class,
                    LegalEntitySeeder::class,
                    SubWarehouseSeeder::class,
                    StorageLocationSeeder::class,
                    ProductTypeSeeder::class,
                    DoctorSeeder::class,
                    HospitalSystemSeeder::class,
                    InstrumentCategorySeeder::class,

                ]);
        $this->call(UserSeeder::class);
        
    }
}