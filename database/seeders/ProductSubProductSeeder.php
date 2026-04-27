<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductSubProduct;

class ProductSubProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productSubProducts = [
            ['name' => 'PUDDU'],
            ['name' => 'ACP'],
            ['name' => 'CANULADOS ARTHREX'],
            ['name' => 'RADIO ARTHREX'],
            ['name' => 'PERONE ARTHREX'],
            ['name' => 'LATARJET TORNILLOS'],
            ['name' => 'PUDDU ARTHREX'],
            ['name' => 'LATARJET'],
            ['name' => 'HOMBRO ARTHREX'],
            ['name' => 'APOLLO'],
            ['name' => 'CANULADOS ACUTRAK DM'],
            ['name' => 'CLAVO FEMUR PROXIMAL DM'],
            ['name' => 'TORNILLOS 4,5 DM'],
            ['name' => 'FEMUR PROXIMAL'],
            ['name' => 'CLAVO FEMUR DISTAL DM'],
            ['name' => 'TORNILLOS 3,5 DM'],
            ['name' => '1/3 CAÑA DM'],
            ['name' => 'RECTAS 4.5'],
            ['name' => 'RECTAS 3.5'],
            ['name' => 'TIBIA PROXIMAL DM'],
            ['name' => 'RADIO DOUBLE MEDICAL'],
            ['name' => 'CLAVO FEMUR UNIVERSAL DM'],
            ['name' => 'CLAVO HUMERO UNIVERSAL DM'],
            ['name' => 'CLAVO TIBIA UNIVERSAL DM'],
            ['name' => 'PELVIS DM'],
            ['name' => 'CLAVICULA DM'],
            ['name' => 'TIBIA DISTAL DM'],
            ['name' => 'HUMERO PROXIMAL DM'],
            ['name' => 'OLECRANON DM'],
            ['name' => 'TIBIA DISTAL 4,5 DM'],
            ['name' => 'PERONE DM'],
            ['name' => 'FEMUR DISTAL DM'],
            ['name' => 'CANULADOS 2 Y 3 MEDARTIS'],
            ['name' => 'SET CODO MEDARTIS'],
            ['name' => 'CANULADOS 4.0 MEDARTIS'],
            ['name' => 'CANULADOS 5.0 MEDARTIS'],
            ['name' => 'CALCANEO MEDARTIS'],
            ['name' => 'MANO MEDARTIS'],
            ['name' => 'CANULADOS 7.0 MEDARTIS'],
            ['name' => 'HALLUX MEDARTIS'],
            ['name' => 'PIE MEDARTIS'],
            ['name' => 'CODO MEDARTIS'],
            ['name' => 'RADIO MEDARTIS'],
            ['name' => 'SPEEDTIP MEDARTIS'],
            ['name' => 'CLAVICULA MEDARTIS'],
            ['name' => 'ARTRODESIS MUÑECA MEDARTIS'],
            ['name' => 'CORONOIDE MEDARTIS'],
            ['name' => 'TOBILLO MEDARTIS'],
            ['name' => 'CANULADOS 1.7 MEDARITS'],
            ['name' => 'SET TOBILLO MEDARTIS'],
            ['name' => 'ARTHOCARE'],
            ['name' => 'CLAVO TEN PROVEEDOR'],
        ];

        foreach ($productSubProducts as $productSubProduct) {
            ProductSubProduct::create($productSubProduct);
        }
    }
}
