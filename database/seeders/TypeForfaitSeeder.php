<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeForfait;

class TypeForfaitSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['nom' => 'Famille'],
            ['nom' => 'Aventure'],
            ['nom' => 'Romantique'],
            ['nom' => 'Luxe'],
            ['nom' => 'Culturel'],
            ['nom' => 'Plage'],
            ['nom' => 'Montagne'],
            ['nom' => 'Circuit'],
        ];

        foreach ($types as $type) {
            TypeForfait::updateOrCreate(['nom' => $type['nom']], $type);
        }
        
        $this->command->info('Types de forfait crees avec succes');
    }
}