<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Circuit court direction-à-direction : un courrier n'exige l'avis de la DG
 * (et donc le circuit complet Protocole -> DGA -> avis DG -> relecture ->
 * signature) que s'il est destiné à la DG elle-même, s'il s'agit d'une
 * demande de stage, ou s'il n'a pas été initié directement par une
 * direction (mail externe entrant via la Réception, toujours trié par le
 * circuit complet). Par défaut à true : toute ligne existante conserve son
 * comportement actuel tant que le data-fix ci-dessous ne la requalifie pas
 * explicitement en circuit court.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->boolean('necessite_avis_dg')->default(true)->after('direction_destination_id');
        });

        // Ne requalifie en circuit court que les courriers encore au statut
        // "recu" (pas encore engagés dans le circuit complet) : un courrier
        // déjà avancé (au_protocole, en_circuit_hierarchique, ...) a été
        // routé sous l'ancien circuit unique et doit le terminer tel quel,
        // sous peine de se retrouver bloqué à une étape sans suivant défini
        // dans la carte de transitions "court".
        DB::table('courriers')
            ->join('users', 'users.id', '=', 'courriers.created_by')
            ->where('users.role', 'responsable_direction')
            ->where('courriers.type', 'correspondance_generale')
            ->where('courriers.statut', 'recu')
            ->whereNotNull('courriers.direction_destination_id')
            ->update(['courriers.necessite_avis_dg' => false]);
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn('necessite_avis_dg');
        });
    }
};
