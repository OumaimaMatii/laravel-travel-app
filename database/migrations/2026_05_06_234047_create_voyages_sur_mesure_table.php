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

        Schema::create('voyages_sur_mesure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voyage_id')->constrained('voyages')->onDelete('cascade');
            $table->decimal('budget_estime', 10, 2);
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('statut_sur_mesure_id')->constrained('statut_sur_mesure')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voyages_sur_mesure');
    }
};
