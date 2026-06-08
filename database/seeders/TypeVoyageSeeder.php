<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeVoyage;

class TypeVoyageSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['nom' => 'forfait'],
            ['nom' => 'sur_mesure'],
        ];

        foreach ($types as $type) {
            TypeVoyage::updateOrCreate(['nom' => $type['nom']], $type);
        }
        
        $this->command->info('Types de voyage crees avec succes');
    }
}