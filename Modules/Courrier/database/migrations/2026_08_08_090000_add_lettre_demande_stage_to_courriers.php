<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Le dépôt professionnel exigeait CV + diplôme d'État + dernier diplôme
 * mais pas la lettre de demande de stage elle-même (document principal du
 * dossier) — corrigé ici, même miroir que les autres pièces professionnelles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('lettre_demande_chemin')->nullable()->after('dernier_diplome_chemin');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn('lettre_demande_chemin');
        });
    }
};
