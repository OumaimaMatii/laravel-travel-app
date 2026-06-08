<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal
        User::updateOrCreate(
            ['email' => 'admin@agence.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Agents
        $agents = [
            ['name' => 'Agent Casablanca', 'email' => 'agent.casa@agence.com'],
            ['name' => 'Agent Marrakech', 'email' => 'agent.marrakech@agence.com'],
            ['name' => 'Agent Rabat', 'email' => 'agent.rabat@agence.com'],
            ['name' => 'Agent Tanger', 'email' => 'agent.tanger@agence.com'],
        ];

        foreach ($agents as $agent) {
            User::updateOrCreate(
                ['email' => $agent['email']],
                [
                    'name' => $agent['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'agent',
                ]
            );
        }

        // Clients
        $clients = [
            ['name' => 'Jean Dupont', 'email' => 'jean.dupont@email.com'],
            ['name' => 'Marie Martin', 'email' => 'marie.martin@email.com'],
            ['name' => 'Pierre Durand', 'email' => 'pierre.durand@email.com'],
            ['name' => 'Sophie Lefevre', 'email' => 'sophie.lefevre@email.com'],
            ['name' => 'Thomas Bernard', 'email' => 'thomas.bernard@email.com'],
            ['name' => 'Julie Petit', 'email' => 'julie.petit@email.com'],
            ['name' => 'Nicolas Robert', 'email' => 'nicolas.robert@email.com'],
            ['name' => 'Laura Richard', 'email' => 'laura.richard@email.com'],
        ];

        foreach ($clients as $client) {
            User::updateOrCreate(
                ['email' => $client['email']],
                [
                    'name' => $client['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'client',
                ]
            );
        }
        
        $this->command->info('Utilisateurs crees avec succes');
    }
}