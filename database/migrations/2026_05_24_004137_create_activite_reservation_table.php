<?php
// database/migrations/2026_05_24_000000_create_activite_reservation_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activite_reservation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->foreignId('activite_id')->constrained('activites')->onDelete('cascade');
            $table->integer('nb_adultes')->default(0);
            $table->integer('nb_enfants')->default(0);
            $table->decimal('prix_unitaire_adulte', 10, 2)->default(0);
            $table->decimal('prix_unitaire_enfant', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activite_reservation');
    }
};