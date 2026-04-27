<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductSubCategory;

class ProductSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subCategories = [
            ['name' => 'Arandela'],
            ['name' => 'Clavo'],
            ['name' => 'Consola'],
            ['name' => 'Instrumental'],
            ['name' => 'Placa'],
            ['name' => 'Tornillo'],
            ['name' => 'Trocar TIP'],
        ];

        foreach ($subCategories as $subCategory) {
            ProductSubCategory::create($subCategory);
        }
    }
}
