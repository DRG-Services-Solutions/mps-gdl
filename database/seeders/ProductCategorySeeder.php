<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Consumibles Quirúrgicos',
                'description' => 'Material de un solo uso o con vida útil limitada,',
            
            ],
            [
                'name' => 'Instrumental Quirúrgico',
                'description' => 'Sets quirúrgicos que requieren esterilización antes y después de cada cirugía.'
            ],
        
        ];
        $now = Carbon::now();

        foreach($categories as $category)
        {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        DB::table('product_categories')->insert($categories);
    }
}
