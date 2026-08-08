<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->string('ville_depart', 100);
            $table->string('ville_arrivee', 100);
            $table->json('points_arret')->nullable();
            $table->dateTime('date_heure_depart');
            $table->unsignedTinyInteger('places_totales');
            $table->unsignedTinyInteger('places_disponibles');
            $table->unsignedInteger('prix_place');
            $table->string('statut')->default('publie');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ville_depart', 'ville_arrivee', 'date_heure_depart']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};