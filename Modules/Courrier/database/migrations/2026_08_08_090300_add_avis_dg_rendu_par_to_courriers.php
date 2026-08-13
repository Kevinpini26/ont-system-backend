<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace qui a réellement rendu l'avis DG — la DG normalement, ou la DGA en
 * intérim (avis_dg_rendu_en_interim=true) lorsque la DG est marquée
 * indisponible. Voir CourrierCircuitService::rendreAvisDg().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->foreignId('avis_dg_rendu_par_id')->nullable()->after('avis_dg_rendu_at')->constrained('users')->nullOnDelete();
            $table->boolean('avis_dg_rendu_en_interim')->default(false)->after('avis_dg_rendu_par_id');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('avis_dg_rendu_par_id');
            $table->dropColumn('avis_dg_rendu_en_interim');
        });
    }
};
