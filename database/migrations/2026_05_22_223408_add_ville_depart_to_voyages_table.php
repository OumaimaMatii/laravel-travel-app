<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voyages', function (Blueprint $table) {
            // Ajouter la colonne ville_depart_id
            $table->foreignId('ville_depart_id')->nullable()->after('destination_id')->constrained('villes');
        });
    }

    public function down(): void
    {
        Schema::table('voyages', function (Blueprint $table) {
            $table->dropForeign(['ville_depart_id']);
            $table->dropColumn('ville_depart_id');
        });
    }
};