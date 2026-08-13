<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')->unique()->constrained('courriers')->cascadeOnDelete();

            // Informations candidat reprises automatiquement du courrier de
            // demande de stage (CourrierStageAvisFavorable), sans ressaisie.
            $table->string('nom');
            $table->string('contact');
            $table->string('etablissement_origine');
            $table->date('periode_debut_demandee')->nullable();
            $table->date('periode_fin_demandee')->nullable();
            $table->string('reference_courrier');

            $table->string('statut', 40);

            // Nommée "direction_id" (et non direction_accueil_id) afin de
            // réutiliser tel quel le BelongsToDirectionScope du Kernel.
            $table->foreignId('direction_id')->nullable()->constrained('directions')->nullOnDelete();
            $table->foreignId('affecte_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('affecte_at')->nullable();

            $table->date('date_debut_stage')->nullable();
            $table->date('date_fin_stage')->nullable();
            $table->timestamp('alerte_echeance_envoyee_at')->nullable();

            $table->decimal('evaluation_direction_note', 4, 2)->nullable();
            $table->text('evaluation_direction_commentaire')->nullable();
            $table->timestamp('evaluation_direction_at')->nullable();

            $table->decimal('evaluation_dfp_note', 4, 2)->nullable();
            $table->text('evaluation_dfp_commentaire')->nullable();
            $table->timestamp('evaluation_dfp_at')->nullable();

            $table->decimal('note_finale', 4, 2)->nullable();
            $table->timestamp('cloture_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
};
