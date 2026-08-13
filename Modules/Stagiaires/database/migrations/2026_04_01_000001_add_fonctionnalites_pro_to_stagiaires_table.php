<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            // Fiche de mission : 2 à 5 objectifs courts, définis par la
            // direction d'accueil au démarrage du stage.
            $table->json('objectifs')->nullable();

            // Détection de doublon (rapprochement approximatif nom +
            // établissement lors de la création automatique).
            $table->boolean('doublon_suspecte')->default(false);
            $table->foreignId('doublon_stagiaire_id')->nullable()->constrained('stagiaires')->nullOnDelete();

            // Convention de stage : PDF généré à la validation de l'arrivée,
            // puis double signature (case à cocher horodatée + identité).
            $table->string('convention_chemin')->nullable();
            $table->timestamp('convention_genere_at')->nullable();
            $table->timestamp('convention_signee_direction_at')->nullable();
            $table->foreignId('convention_signee_direction_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('convention_signee_stagiaire_at')->nullable();

            // Dérogation de quota (voir stagiaire_liens_publics et le
            // journal d'audit pour la justification associée).
            $table->boolean('affecte_hors_quota')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->dropConstrainedForeignId('doublon_stagiaire_id');
            $table->dropConstrainedForeignId('convention_signee_direction_par_id');
            $table->dropColumn([
                'objectifs',
                'doublon_suspecte',
                'convention_chemin',
                'convention_genere_at',
                'convention_signee_direction_at',
                'convention_signee_stagiaire_at',
                'affecte_hors_quota',
            ]);
        });
    }
};
