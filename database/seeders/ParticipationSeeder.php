<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Participation;
use App\Models\Donateur;
use App\Models\Campagne;

class ParticipationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $donateur = Donateur::inRandomOrder()->first();
        $campagne = Campagne::inRandomOrder()->first();

        if ($donateur && $campagne) {
            Participation::create([
                'donateur_id' => $donateur->id,
                'campagne_id' => $campagne->id,
                'statut' => 'en attente',
            ]);
        } else {
            $this->command->warn('Aucun donateur ou campagne disponible pour créer une participation.');
        }
    }
}
