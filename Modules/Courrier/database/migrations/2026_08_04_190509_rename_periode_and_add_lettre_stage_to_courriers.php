<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `candidat_periode_debut/fin` renommés en `periode_souhaitee_debut/fin` :
 * ce sont les dates que le candidat aimerait, exprimées au dépôt de sa
 * lettre, avant tout examen du dossier — jamais les dates officielles du
 * stage (celles-ci vivent exclusivement sur Stagiaire.date_debut_stage /
 * date_fin_stage, fixées par la DFP à validerArrivee(), sans copie
 * automatique depuis ces champs). Le nom précédent laissait penser à tort
 * qu'il s'agissait de dates arrêtées.
 *
 * `lettre_stage_chemin` : la pièce justificative que l'établissement
 * d'origine délivre pour introduire la demande (pas un texte de
 * motivation libre — voir DocumentType::LETTRE_STAGE_UNIVERSITE).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->renameColumn('candidat_periode_debut', 'periode_souhaitee_debut');
            $table->renameColumn('candidat_periode_fin', 'periode_souhaitee_fin');
            $table->string('lettre_stage_chemin')->nullable()->after('candidat_email');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->renameColumn('periode_souhaitee_debut', 'candidat_periode_debut');
            $table->renameColumn('periode_souhaitee_fin', 'candidat_periode_fin');
            $table->dropColumn('lettre_stage_chemin');
        });
    }
};
