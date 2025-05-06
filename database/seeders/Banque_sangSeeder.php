<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BanqueSang;
use App\Models\StructureTransfusionSanguin;

class Banque_sangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banque_sangs = [
            [
                'nombre_poche' => 50,
                'stock_actuelle' => 35,
                'date_mise_a_jour' => now(),
                'statut' => 'disponible',
                'date_expiration' => now()->addDays(30)->toDateString(),
                'heure_expiration' => '12:00',
                'date_dernier_stock' => now()->subDays(10)->toDateString(),
                'date_dernier_approvisionnement' => now()->subDays(5)->toDateString(),
                'date_dernier_rapprochement' => now()->subDays(3)->toDateString(),
                'groupe_sanguin_id' => 1,
                'structure_transfusion_sanguin_id' => 1,
            ],
            [
                'nombre_poche' => 20,
                'stock_actuelle' => 10,
                'date_mise_a_jour' => now(),
                'statut' => 'disponible',
                'date_expiration' => now()->addDays(20)->toDateString(),
                'heure_expiration' => '10:00',
                'date_dernier_stock' => now()->subDays(15)->toDateString(),
                'date_dernier_approvisionnement' => now()->subDays(7)->toDateString(),
                'date_dernier_rapprochement' => now()->subDays(4)->toDateString(),
                'groupe_sanguin_id' => 1,
                'structure_transfusion_sanguin_id' => 2,
            ],
            [
                'nombre_poche' => 30,
                'stock_actuelle' => 20,
                'date_mise_a_jour' => now(),
                'statut' => 'indisponible',
                'date_expiration' => now()->addDays(15)->toDateString(),
                'heure_expiration' => '14:00',
                'date_dernier_stock' => now()->subDays(7)->toDateString(),
                'date_dernier_approvisionnement' => now()->subDays(2)->toDateString(),
                'date_dernier_rapprochement' => now()->subDays(1)->toDateString(),
                'groupe_sanguin_id' => 2,
                'structure_transfusion_sanguin_id' => 2,
            ]
        ];

        foreach ($banque_sangs as $banque_sang) {
            BanqueSang::create($banque_sang);

        }
    }
}