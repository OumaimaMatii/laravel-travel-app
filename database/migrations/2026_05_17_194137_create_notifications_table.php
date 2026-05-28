<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('titre');
            $table->text('message');
            $table->enum('type', [
                'reservation', 'paiement', 'rappel', 'alerte', 'document', 'info', 'annulation'
            ])->default('info');
            $table->string('lien')->nullable();
            $table->boolean('lue')->default(false);
            $table->timestamp('lue_le')->nullable();
            $table->timestamp('envoyee_le')->useCurrent();
            $table->timestamps();
            
            $table->index(['user_id', 'lue', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};