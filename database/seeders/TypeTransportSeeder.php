<?php
// database/seeders/TypeTransportSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TypeTransportSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['nom' => 'Avion', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Train', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Bus', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Tramway', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Taxi', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            // 4 types de bus pour les forfaits
            ['nom' => 'Mini-bus VIP (8 places)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Bus Standard (30 places)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Bus Grand Tourisme (38 places)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['nom' => 'Bus Luxe (52 places)', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ];

        DB::table('type_transports')->insert($types);
    }
}