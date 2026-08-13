<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ligne singleton (une seule ligne, quel que soit son id) : un seul
        // état partagé pour toute la plateforme, pas un réglage par
        // utilisateur — voir DisponibiliteDemandesStage::actuelle().
        Schema::create('disponibilite_demandes_stage', function (Blueprint $table) {
            $table->id();
            $table->boolean('academique_ouvert')->default(true);
            $table->boolean('professionnel_ouvert')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilite_demandes_stage');
    }
};
