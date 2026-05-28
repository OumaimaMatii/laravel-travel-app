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

        Schema::create('voyages_forfait', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voyage_id')->constrained('voyages')->onDelete('cascade');
            $table->decimal('prix_adulte', 10, 2);
            $table->decimal('prix_enfant', 10, 2)->nullable();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->foreignId('statut_forfait_id')->constrained('statut_forfait')->onDelete('cascade');
            $table->text('programme');
            $table->integer('nombre_places')->default(0);
            $table->integer('places_restantes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voyages_forfait');
    }
};
