<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique des prolongations de stage professionnel : chaque prolongation
 * ajoute une ligne, jamais d'écrasement de l'historique précédent (voir
 * StagiaireCircuitService::prolongerStage()). `created_at` sert de date de
 * prolongation — pas de colonne dédiée redondante.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaire_prolongations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->cascadeOnDelete();
            $table->date('ancienne_date_fin');
            $table->date('nouvelle_date_fin');
            $table->text('motif');
            $table->foreignId('prolonge_par_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaire_prolongations');
    }
};
