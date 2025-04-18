<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        
        

        $user = User::create([
            'nom' => "Hapsatou",
            'prenom' => "Thiam",
            'email' => "hapsatou.thiam@gmail.com",
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),

         
        ]);
        $user->assignRole($adminRole);

    }
}
