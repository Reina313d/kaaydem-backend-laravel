<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('utilisateur_signale_id')->constrained('users')->cascadeOnDelete();
            $table->text('motif');
            $table->string('statut')->default('ouvert'); // ouvert|en_cours_traitement|resolu|rejete
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
