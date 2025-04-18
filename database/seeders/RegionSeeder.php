<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['libelle' => 'Dakar'],
            ['libelle' => 'Diourbel'],
            ['libelle' => 'Fatick'],
            ['libelle' => 'Kaffrine'],
            ['libelle' => 'Kaolack'],
            ['libelle' => 'Kédougou'],
            ['libelle' => 'Kolda'],
            ['libelle' => 'Louga'],
            ['libelle' => 'Matam'],
            ['libelle' => 'Saint-Louis'],
            ['libelle' => 'Sédhiou'],
            ['libelle' => 'Tambacounda'],
            ['libelle' => 'Thiès'],
            ['libelle' => 'Ziguinchor'],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
