<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace la note libre /20 par la grille officielle ONT (3 sections,
 * /50+/30+/20 = /100) — voir Modules\Stagiaires\Support\GrilleEvaluation.
 * Ajoute aussi le verrou DFP sur l'accès de la direction au formulaire
 * d'évaluation (periode_evaluation_ouverte_at).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->dropColumn([
                'evaluation_direction_note',
                'evaluation_direction_commentaire',
                'evaluation_dfp_note',
                'evaluation_dfp_commentaire',
            ]);

            $table->json('evaluation_direction_grille')->nullable()->after('evaluation_direction_at');
            $table->decimal('evaluation_direction_total', 5, 2)->nullable()->after('evaluation_direction_grille');

            $table->json('evaluation_dfp_grille')->nullable()->after('evaluation_dfp_at');
            $table->decimal('evaluation_dfp_total', 5, 2)->nullable()->after('evaluation_dfp_grille');

            $table->timestamp('periode_evaluation_ouverte_at')->nullable()->after('date_fin_stage');
            $table->foreignId('periode_evaluation_ouverte_par_id')->nullable()->after('periode_evaluation_ouverte_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->dropForeign(['periode_evaluation_ouverte_par_id']);
            $table->dropColumn([
                'evaluation_direction_grille',
                'evaluation_direction_total',
                'evaluation_dfp_grille',
                'evaluation_dfp_total',
                'periode_evaluation_ouverte_at',
                'periode_evaluation_ouverte_par_id',
            ]);

            $table->decimal('evaluation_direction_note', 4, 2)->nullable();
            $table->text('evaluation_direction_commentaire')->nullable();
            $table->decimal('evaluation_dfp_note', 4, 2)->nullable();
            $table->text('evaluation_dfp_commentaire')->nullable();
        });
    }
};
