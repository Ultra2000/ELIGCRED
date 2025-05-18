<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Réinitialiser les tables
        Role::truncate();
        Permission::truncate();

        // Créer les permissions
        $permissions = [
            // Gestion des utilisateurs
            ['name' => 'Gérer les utilisateurs', 'slug' => 'manage-users'],
            ['name' => 'Voir les utilisateurs', 'slug' => 'view-users'],
            
            // Gestion des dossiers
            ['name' => 'Créer des dossiers', 'slug' => 'create-dossiers'],
            ['name' => 'Modifier des dossiers', 'slug' => 'edit-dossiers'],
            ['name' => 'Supprimer des dossiers', 'slug' => 'delete-dossiers'],
            ['name' => 'Voir les dossiers', 'slug' => 'view-dossiers'],
            ['name' => 'Charger des dossiers', 'slug' => 'upload-dossiers'],
            
            // Gestion des avis
            ['name' => 'Donner des avis', 'slug' => 'give-avis'],
            ['name' => 'Modifier des avis', 'slug' => 'edit-avis'],
            ['name' => 'Voir les avis', 'slug' => 'view-avis'],
            
            // Validation des dossiers
            ['name' => 'Valider les dossiers', 'slug' => 'validate-dossiers'],
            ['name' => 'Rejeter les dossiers', 'slug' => 'reject-dossiers'],
            
            // Rapports et statistiques
            ['name' => 'Voir les rapports', 'slug' => 'view-reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Créer les rôles correspondant aux profils
        $roles = [
            [
                'name' => 'Administrateur',
                'slug' => 'admin',
                'description' => 'Gère tout',
                'permissions' => Permission::all()->pluck('slug')->toArray(),
            ],
            [
                'name' => 'Préposé au Chargement',
                'slug' => 'pc',
                'description' => 'Charger dossier',
                'permissions' => [
                    'view-dossiers',
                    'upload-dossiers',
                    'view-avis',
                ],
            ],
            [
                'name' => 'Responsable d\'Étude',
                'slug' => 're',
                'description' => 'Soumission dossier',
                'permissions' => [
                    'create-dossiers',
                    'edit-dossiers',
                    'view-dossiers',
                    'view-avis',
                ],
            ],
            [
                'name' => 'Membre du Comité de Crédit',
                'slug' => 'mcc',
                'description' => 'Donne avis',
                'permissions' => [
                    'view-dossiers',
                    'give-avis',
                    'edit-avis',
                    'view-avis',
                ],
            ],
            [
                'name' => 'Président du Comité de Crédit',
                'slug' => 'pcc',
                'description' => 'Décide',
                'permissions' => [
                    'view-dossiers',
                    'give-avis',
                    'edit-avis',
                    'view-avis',
                    'validate-dossiers',
                    'reject-dossiers',
                    'view-reports',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::create($roleData);
            $role->permissions()->attach(
                Permission::whereIn('slug', $permissions)->get()
            );
        }
    }
} 