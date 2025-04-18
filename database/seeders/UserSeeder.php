<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;



class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $donateurRole = Role::firstOrCreate(['name' => 'Donateur']);
        $organisateurRole = Role::firstOrCreate(['name' => 'Organisateur']);
        $structureRole = Role::firstOrCreate(['name' => 'Structure de transfusion sanguine']);

        // Admin
        $admin = User::create([
            'nom' => 'Admin Principal',
            'telephone' => 770000001,
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'region_id' => 1, // Assurez-vous que la région avec l'ID 1 existe
            'remember_token' => Str::random(10),
        ]);
        $admin->assignRole($adminRole);

        // Donateur
        $donateur = User::create([
            'nom' => 'Mamadou Diallo',
            'telephone' => 770000002,
            'email' => 'donateur@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'region_id' => 2, // Assurez-vous que la région avec l'ID 1 existe
            'remember_token' => Str::random(10),
        ]);
        $donateur->assignRole($donateurRole);

        // Organisateur
        $organisateur = User::create([
            'nom' => 'Seynabou Diop',
            'telephone' => 770000003,
            'email' => 'organisateur@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'region_id' => 3, // Assurez-vous que la région avec l'ID 1 existe
            'remember_token' => Str::random(10),
        ]);
        $organisateur->assignRole($organisateurRole);

        // Structure de transfusion sanguine
        $structureUser = User::create([
            'nom' => 'Centre National',
            'telephone' => 770000004,
            'email' => 'structure@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'region_id' => 4, // Assurez-vous que la région avec l'ID 1 existe
            'remember_token' => Str::random(10),
        ]);
        $structureUser->assignRole($structureRole);
    }
}

