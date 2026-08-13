<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retours d'expérience des stagiaires : table volontairement séparée de
 * `stagiaires` et jamais référencée par StagiaireResource (visible par la
 * direction d'accueil), afin qu'aucune fuite accidentelle ne compromette la
 * confidentialité vis-à-vis de la direction — seule la DFP y accède
 * (StagiairePolicy::voirRetour).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaire_retours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->unique()->constrained('stagiaires')->cascadeOnDelete();
            $table->unsignedTinyInteger('note_encadrement');
            $table->unsignedTinyInteger('note_missions');
            $table->unsignedTinyInteger('note_ambiance');
            $table->text('commentaire')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaire_retours');
    }
};
