<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campagne;
class CampagneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campagnes = [
                [
                'theme' => 'Don de sang pour sauver des vies',
                'description' => 'Campagne de don de sang organisée par Sunu Santé',
                'lieu' => 'Centre de Transfusion Sanguine de Dakar',
                'date_debut' => '2025-04-10',
                'date_fin' => '2025-04-12',
                'Heure_debut' => '09:00',
                'Heure_fin' => '17:00',
                'participant' => '100',
                'statut' => 'à venir',
                'organisateur_id' => 1,
                'structure_transfusion_sanguin_id' => 1,
            ],
            [
                'theme' => 'Collecte de sang pour les urgences',
                'description' => 'Campagne de collecte de sang pour les urgences médicales',
                'lieu' => 'Hôpital Principal de Dakar',
                'date_debut' => '2023-10-15',
                'date_fin' => '2023-10-20',
                'Heure_debut' => '08:00',
                'Heure_fin' => '16:00',
                'participant' => '50',
                'statut' => 'en cours',
                'organisateur_id' => 1,
                'structure_transfusion_sanguin_id' => 1,
            ],
            [
                'theme' => 'Campagne de sensibilisation au don de sang',
                'description' => 'Sensibilisation sur l’importance du don de sang',
                'lieu' => 'Université Cheikh Anta Diop de Dakar',
                'date_debut' => '2023-11-01',
                'date_fin' => '2023-11-05',
                'Heure_debut' => '10:00',
                'Heure_fin' => '18:00',
                'participant' => '200',
                'statut' => 'à venir',
                'organisateur_id' => 1,
                'structure_transfusion_sanguin_id' => 1,
            ],
        ];

        foreach ($campagnes as $campagne) {
            Campagne::create($campagne);
        }
    }
}
