<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisateur;
use App\Models\User;

class OrganisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Récupérer l'utilisateur avec le rôle Organisateur
        $user = User::where('email', 'organisateur@gmail.com')->first();

        if ($user) {
            $organisateurs = [
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Fall',
                    'adresse' => 'Dakar Plateau',
                    'type_organisation' => 'Organisation non gouvernementale (ONG)',
                    'structure_transfusion_sanguin_id' => 1, // Assurez-vous que cet ID existe dans structures_transfusion_sanguine

                ],
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Ndiaye',
                    'adresse' => 'Pikine',
                    'type_organisation' => 'Hôpital',
                    'structure_transfusion_sanguin_id' => 2, // Assurez-vous que cet ID existe dans structures_transfusion_sanguine
                ],
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Sow',
                    'adresse' => 'Guédiawaye',
                    'type_organisation' => 'Clinique',
                    'structure_transfusion_sanguin_id' => 2, // Assurez-vous que cet ID existe dans structures_transfusion_sanguine
                ],
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Kane',
                    'adresse' => 'Thiès',
                    'type_organisation' => 'Centre de santé',
                    'structure_transfusion_sanguin_id' => 2, // Assurez-vous que cet ID existe dans structures_transfusion_sanguine
                ],
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Diallo',
                    'adresse' => 'Kaolack',
                    'telephone' => '776655443',
                    'type_organisation' => 'Laboratoire',
                    'structure_transfusion_sanguin_id' => 1, // Assurez-vous que cet ID existe dans structures_transfusion_sanguine

                ],
                [
                    'user_id' => $user->id,
                    'nom_responsable' => 'Dr Ndiaye',
                    'adresse' => 'Saint-Louis',
                    'telephone' => '775544332',
                    'type_organisation' => 'Pharmacie',
                    'structure_transfusion_sanguin_id' => 1, // Assurez-vous que cet ID existe dans structures_transfusion_sanguine
                ],
            ];

            foreach ($organisateurs as $data) {
                // On vérifie d'abord s'il existe déjà pour éviter les doublons
                Organisateur::firstOrCreate(
                    ['user_id' => $data['user_id']],
                    $data
                );
            }
        }
    }
}