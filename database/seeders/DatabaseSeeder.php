<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\DonateurSeeder;
use Database\Seeders\OrganisateurSeeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\GroupeSanguinSeeder;
use Database\Seeders\ParticipationSeeder;
use Database\Seeders\CampagneSeeder;
use Database\Seeders\RegionSeeder;
use Database\Seeders\Structure_Transfusion_SanguinsSeeder;
use Database\Seeders\Banque_sangSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            UserSeeder::class,
            Groupes_sanguinsSeeder::class,
            Structure_Transfusion_SanguinsSeeder::class,
            OrganisateurSeeder::class,
            CampagneSeeder::class,
            DonateurSeeder::class,
            Banque_sangSeeder::class,
            AdminSeeder::class,
                        
        ]);
        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
   
}
