<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Courrier sortant initié par la DG elle-même (instruction, note de
 * service), sans courrier entrant déclencheur — voir
 * CourrierCircuitService::initierParDg(). `initie_par_dg` sert à la fois de
 * sélecteur de circuit (voir config('courrier.circuit_transitions.dg_initie'))
 * et de marqueur de traçabilité affiché sur la fiche. `validation_dg_requise`
 * est la case à cocher du formulaire de rédaction : si cochée, le courrier
 * passe par `en_attente_validation_dg` avant la relecture ; sinon la
 * relecture démarre immédiatement. `valide_par_dg_at` trace le moment de
 * cette validation, sur le même modèle que `avis_dg_rendu_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->boolean('initie_par_dg')->default(false)->after('necessite_avis_dg');
            $table->boolean('validation_dg_requise')->default(false)->after('initie_par_dg');
            $table->timestamp('valide_par_dg_at')->nullable()->after('validation_dg_requise');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn(['initie_par_dg', 'validation_dg_requise', 'valide_par_dg_at']);
        });
    }
};
