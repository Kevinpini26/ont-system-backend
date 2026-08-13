<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Politique de conservation des données : les candidatures de stage non
 * retenues (avis DG défavorable) sont anonymisées 12 mois après la
 * décision — voir AnonymiserCandidaturesNonRetenuesCommand.
 *
 * `avis_dg_rendu_at` fixe précisément le point de départ du délai de
 * conservation (distinct de `created_at`, qui date la réception du
 * courrier, pas la décision) ; `anonymise_at` marque le dossier comme
 * déjà traité, pour ne jamais le retraiter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->timestamp('avis_dg_rendu_at')->nullable()->after('avis_dg_commentaire');
            $table->timestamp('anonymise_at')->nullable()->after('relance_avis_dg_envoyee_at');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn(['avis_dg_rendu_at', 'anonymise_at']);
        });
    }
};
