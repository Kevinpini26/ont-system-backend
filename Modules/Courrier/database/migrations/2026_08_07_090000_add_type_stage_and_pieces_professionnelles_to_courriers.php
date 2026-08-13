<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Second type de stage : le candidat choisit au dépôt entre "académique"
 * (pièce déjà en place : lettre_stage_chemin) et "professionnel" (3 pièces
 * distinctes). Colonnes nullables, comme lettre_stage_chemin — seuls les
 * courriers de type demande_stage les renseignent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('type_stage')->nullable()->after('candidat_email');
            $table->string('cv_chemin')->nullable()->after('lettre_stage_chemin');
            $table->string('diplome_etat_chemin')->nullable()->after('cv_chemin');
            $table->string('dernier_diplome_chemin')->nullable()->after('diplome_etat_chemin');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn(['type_stage', 'cv_chemin', 'diplome_etat_chemin', 'dernier_diplome_chemin']);
        });
    }
};
