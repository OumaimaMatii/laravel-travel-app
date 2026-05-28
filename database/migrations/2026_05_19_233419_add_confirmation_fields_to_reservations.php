<?php
// database/migrations/2026_05_20_000001_add_confirmation_fields_to_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pour les réservations
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('confirmation_deadline')->nullable()->after('statut');
            $table->boolean('notification_envoyee')->default(false)->after('confirmation_deadline');
            $table->string('type_verification')->default('forfait')->after('notification_envoyee'); // forfait ou sur_mesure
        });
        
        // Pour les transports (réservation temporaire)
        Schema::table('transports', function (Blueprint $table) {
            $table->integer('places_reservees_temp')->default(0)->after('places_disponibles');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['confirmation_deadline', 'notification_envoyee', 'type_verification']);
        });
        
        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn('places_reservees_temp');
        });
    }
};