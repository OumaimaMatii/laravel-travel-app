<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            // Ajouter ville de départ (clé étrangère)
            if (!Schema::hasColumn('transports', 'ville_depart_id')) {
                $table->foreignId('ville_depart_id')
                      ->nullable()
                      ->after('compagnie')
                      ->constrained('villes')
                      ->onDelete('set null');
            }
            
            // Ajouter ville d'arrivée (clé étrangère)
            if (!Schema::hasColumn('transports', 'ville_arrivee_id')) {
                $table->foreignId('ville_arrivee_id')
                      ->nullable()
                      ->after('ville_depart_id')
                      ->constrained('villes')
                      ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropForeign(['ville_depart_id']);
            $table->dropForeign(['ville_arrivee_id']);
            $table->dropColumn(['ville_depart_id', 'ville_arrivee_id']);
        });
    }
};