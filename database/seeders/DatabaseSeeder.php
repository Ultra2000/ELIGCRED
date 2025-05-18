<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Création du profil administrateur
        $adminProfile = \App\Models\Profile::create([
            'libelle' => 'Administrateur',
            'code' => 'ADMIN',
        ]);

        // Création de l'utilisateur administrateur
        User::factory()->create([
            'name' => 'Admin',
            'prenom' => 'System',
            'email' => 'admin@example.com',
            'profile_id' => $adminProfile->id,
        ]);

        $this->call([
            RoleAndPermissionSeeder::class,
        ]);
    }
}
