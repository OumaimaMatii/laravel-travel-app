<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('titre');
            $table->enum('type', [
                'billet_avion', 'billet_train', 'confirmation_hotel', 
                'itineraire', 'assurance', 'facture', 'visa', 'autre'
            ]);
            $table->string('chemin_fichier');
            $table->string('nom_fichier_original');
            $table->integer('taille')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};