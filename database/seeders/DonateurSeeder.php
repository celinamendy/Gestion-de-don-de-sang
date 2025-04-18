<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Donateur;
use App\Models\User;
class DonateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Récupérer l'utilisateur qui a le rôle "Donateur"
         $user = User::where('email', 'donateur@gmail.com')->first();

         if ($user) {
             $donateurData = [
                 'user_id' => $user->id,
                 'adresse' => 'Pikine',
                 'date_naissance' => '1995-06-15',
                 'sexe' => 'F',
                 'groupe_sanguin_id' => 1,
                 'poids' => '60kg',
                 'antecedent_medicament' => 'Aucun',
                
             ];
 
                // Créer le donateur avec les données fournies
                // foreach ($Donateur as $donateurData) {
                //     $donateur = Donateur::create($donateurData);
                // }
             Donateur::create($donateurData);
         }
     }
}
