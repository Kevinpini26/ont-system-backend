<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace le pointage par période (date_debut/date_fin/present) par un
 * pointage quotidien arrivée/départ : une ligne par jour ouvré, saisie en
 * deux temps par la DFP (arrivée le matin, départ le soir — voir
 * StagiaireCircuitService::enregistrerPresence(), upsert sur
 * stagiaire_id+date). Modèle de données incompatible avec l'ancien (pas de
 * correspondance 1:1 entre une période et un jour précis) : les lignes
 * existantes sont vidées plutôt que converties.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('stagiaire_presences')->truncate();

        Schema::table('stagiaire_presences', function (Blueprint $table) {
            $table->dropForeign(['enregistre_par_id']);
            $table->dropColumn(['date_debut', 'date_fin', 'present', 'enregistre_par_id']);

            $table->date('date')->after('stagiaire_id');
            $table->time('heure_arrivee')->nullable()->after('date');
            $table->time('heure_depart')->nullable()->after('heure_arrivee');
            $table->foreignId('saisi_par_id')->after('heure_depart')->constrained('users')->cascadeOnDelete();

            $table->unique(['stagiaire_id', 'date']);
        });
    }

    /**
     * ATTENTION — réversibilité de schéma uniquement, pas de données : up()
     * a vidé la table avant de restructurer (voir docblock ci-dessus), donc
     * ce down() recrée fidèlement l'ancien schéma mais sur une table vide.
     * Les présences saisies entre l'exécution de up() et un éventuel
     * rollback sont irrémédiablement perdues — aucun down() ne peut les
     * ressusciter, la donnée source n'existe plus. Ne pas rollback cette
     * migration sur un environnement avec des présences réelles sans en
     * avoir conscience.
     */
    public function down(): void
    {
        Schema::table('stagiaire_presences', function (Blueprint $table) {
            $table->dropUnique(['stagiaire_id', 'date']);
            $table->dropForeign(['saisi_par_id']);
            $table->dropColumn(['date', 'heure_arrivee', 'heure_depart', 'saisi_par_id']);

            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('present')->default(true);
            $table->foreignId('enregistre_par_id')->constrained('users')->cascadeOnDelete();
        });
    }
};
