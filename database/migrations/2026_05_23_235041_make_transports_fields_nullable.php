<?php
// database/migrations/2026_05_24_000001_make_transports_fields_nullable.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->string('numero_vol')->nullable()->change();
            $table->datetime('heure_depart')->nullable()->change();
            $table->datetime('heure_arrivee')->nullable()->change();
            $table->string('depart')->nullable()->change();
            $table->string('arrivee')->nullable()->change();
            $table->decimal('prix', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->string('numero_vol')->nullable(false)->change();
            $table->datetime('heure_depart')->nullable(false)->change();
            $table->datetime('heure_arrivee')->nullable(false)->change();
            $table->string('depart')->nullable(false)->change();
            $table->string('arrivee')->nullable(false)->change();
            $table->decimal('prix', 10, 2)->nullable(false)->change();
        });
    }
};