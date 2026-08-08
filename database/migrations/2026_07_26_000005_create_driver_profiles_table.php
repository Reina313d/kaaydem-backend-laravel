<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('numero_permis');
            $table->string('vehicule_marque');
            $table->string('vehicule_modele');
            $table->string('vehicule_immatriculation');
            $table->string('statut_validation')->default('en_attente'); // en_attente | valide | rejete
            $table->string('motif_rejet')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_profiles');
    }
};
