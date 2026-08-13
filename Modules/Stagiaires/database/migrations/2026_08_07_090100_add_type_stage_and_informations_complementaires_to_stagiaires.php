<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `type_stage` : not null avec défaut 'academique' — préserve tel quel le
 * comportement des dossiers déjà existants et de la suite de tests, aucun
 * n'a besoin de le renseigner explicitement pour continuer à fonctionner.
 *
 * Champs "informations complémentaires" (fiche officielle ONT) : renseignés
 * progressivement, jamais obligatoires au dépôt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->string('type_stage')->default('academique')->after('etablissement_origine');
            $table->string('lieu_naissance')->nullable()->after('type_stage');
            $table->string('filiere_formation')->nullable()->after('lieu_naissance');
            $table->string('niveau_formation')->nullable()->after('filiere_formation');
            $table->string('maitre_stage')->nullable()->after('niveau_formation');
            $table->string('conseiller_stage')->nullable()->after('maitre_stage');
        });
    }

    public function down(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->dropColumn([
                'type_stage', 'lieu_naissance', 'filiere_formation',
                'niveau_formation', 'maitre_stage', 'conseiller_stage',
            ]);
        });
    }
};
