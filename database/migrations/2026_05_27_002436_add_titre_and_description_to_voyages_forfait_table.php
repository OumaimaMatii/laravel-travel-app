<?php
// database/migrations/2026_05_27_xxxxxx_add_titre_and_description_to_voyages_forfait_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voyages_forfait', function (Blueprint $table) {
            // Ajouter la colonne titre
            if (!Schema::hasColumn('voyages_forfait', 'titre')) {
                $table->string('titre', 255)->nullable()->after('programme');
            }
            
            // Ajouter la colonne description
            if (!Schema::hasColumn('voyages_forfait', 'description')) {
                $table->text('description')->nullable()->after('titre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('voyages_forfait', function (Blueprint $table) {
            if (Schema::hasColumn('voyages_forfait', 'titre')) {
                $table->dropColumn('titre');
            }
            if (Schema::hasColumn('voyages_forfait', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};