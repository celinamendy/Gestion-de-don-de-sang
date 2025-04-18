<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Groupe_sanguin;
class Groupes_sanguinsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupes_sanguins = [
            ['libelle' => 'A+'],
            ['libelle' => 'A-'],
            ['libelle' => 'B+'],
            ['libelle' => 'B-'],
            ['libelle' => 'AB+'],
            ['libelle' => 'AB-'],
            ['libelle' => 'O+'],
            ['libelle' => 'O-'],
        ];

        foreach ($groupes_sanguins as $groupe_sanguin) {
            Groupe_sanguin::create($groupe_sanguin);
        }
    }
}
