<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatutForfait;
use App\Models\StatutSurMesure;

class StatutSeeder extends Seeder
{
    public function run(): void
    {
        // Statuts pour les forfaits
        $statutsForfait = [
            ['nom' => 'Disponible'],
            ['nom' => 'Complet'],
            ['nom' => 'Archive'],
            ['nom' => 'Promotion'],
        ];

        foreach ($statutsForfait as $statut) {
            StatutForfait::updateOrCreate(['nom' => $statut['nom']], $statut);
        }

        // Statuts pour les voyages sur mesure
        $statutsSurMesure = [
            ['nom' => 'En attente'],
            ['nom' => 'En validation'],
            ['nom' => 'Valide'],
            ['nom' => 'Refuse'],
            ['nom' => 'En cours de traitement'],
            ['nom' => 'Devis envoye'],
            ['nom' => 'Facture envoyee'],
        ];

        foreach ($statutsSurMesure as $statut) {
            StatutSurMesure::updateOrCreate(['nom' => $statut['nom']], $statut);
        }
        
        $this->command->info('Statuts crees avec succes');
    }
}