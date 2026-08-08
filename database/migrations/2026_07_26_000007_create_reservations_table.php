<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('nombre_places');
            $table->string('statut')->default('en_attente'); // en_attente|confirmee|terminee|annulee|refusee
            $table->json('historique_transitions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trip_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
