<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StructureTransfusionSanguin;
class Structure_Transfusion_SanguinsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'utilisateur avec le rôle "Structure de transfusion sanguine"
        $user = User::where('email', 'structure@gmail.com')->first();

        if ($user) {
            $structure = [
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Diouf',
                    'type_entite' => 'Hôpital',
                    'adresse' => 'Dakar Plateau',
                ],
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Sow',
                    'type_entite' => 'poste de santé',
                    'adresse' => 'Pikine',
                ],
            ];
                StructureTransfusionSanguin::create($structure);

            
        }
    }
}