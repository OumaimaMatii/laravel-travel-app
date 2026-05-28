<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('montant_total', 10, 2)->after('statut')->default(0);
            $table->string('mode_paiement')->nullable()->after('montant_total');
            $table->date('date_paiement')->nullable()->after('mode_paiement');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['montant_total', 'mode_paiement', 'date_paiement']);
        });
    }
};