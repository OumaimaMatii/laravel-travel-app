<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voyages_forfait', function (Blueprint $table) {
            // Ajouter la colonne transport_id
            $table->foreignId('transport_id')->nullable()->after('hotel_id')->constrained('transports');
        });
    }

    public function down(): void
    {
        Schema::table('voyages_forfait', function (Blueprint $table) {
            $table->dropForeign(['transport_id']);
            $table->dropColumn('transport_id');
        });
    }
};