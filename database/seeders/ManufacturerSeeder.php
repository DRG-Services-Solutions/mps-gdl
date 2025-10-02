<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manufacturers = [
            [
                'name' => '3M Healt Care',
                'description' => 'Fabricante global de productos médicos y quirúrgicos, incluyendo esparadrapos, vendajes y equipos de esterilización.'
            ],
            [
                'name' => 'Johnson & Johnson Medical',
                'description' => 'Proveedor líder de dispositivos médicos, instrumental quirúrgico y productos de cuidado de heridas.',
            ],
            [
                'name' => 'Arthrex',
                'description' => 'Arthrex is a global medical device company and leader in multispecialty minimally invasive surgical technology innovation.'
            ]
            ];

            $now = Carbon::now();
            foreach ($manufacturers as &$manufacturer) {
            $manufacturer['created_at'] = $now;
            $manufacturer['updated_at'] = $now;
            }

            DB::table('manufacturers')->insert($manufacturers);
    }
}
