<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voyages_forfait', function (Blueprint $table) {
            $table->foreignId('type_forfait_id')
                  ->after('agent_id')
                  ->nullable()
                  ->constrained('type_forfaits')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('voyages_forfait', function (Blueprint $table) {
            $table->dropForeign(['type_forfait_id']);
            $table->dropColumn('type_forfait_id');
        });
    }
};