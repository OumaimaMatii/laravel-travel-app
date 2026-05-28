<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
          Schema::disableForeignKeyConstraints();

        Schema::create('transports', function (Blueprint $table) {
            $table->id();
           
            $table->string('compagnie');
            $table->string('numero_vol');
            $table->string('depart');
            $table->string('arrivee');
            $table->datetime('heure_depart');
            $table->datetime('heure_arrivee');
            $table->decimal('prix', 10, 2);
            $table->integer('places_disponibles');
            $table->foreignId('type_transport_id')->constrained('type_transports')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};
