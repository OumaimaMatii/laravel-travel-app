<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeTransport;

class TypeTransportSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['nom' => 'Bus 50 places'],
            ['nom' => 'Bus 30 places'],
            ['nom' => 'Mini-bus 20 places'],
            ['nom' => 'Minibus 15 places'],
            ['nom' => 'Train'],
            ['nom' => 'Avion'],
            ['nom' => 'Voiture particuliere'],
        ];

        foreach ($types as $type) {
            TypeTransport::updateOrCreate(['nom' => $type['nom']], $type);
        }
        
        $this->command->info('Types de transport crees avec succes');
    }
}