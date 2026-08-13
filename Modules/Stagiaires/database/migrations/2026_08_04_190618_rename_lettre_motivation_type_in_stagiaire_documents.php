<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration de données : DocumentType::LETTRE_MOTIVATION ('lettre_motivation')
 * devient LETTRE_STAGE_UNIVERSITE ('lettre_stage_universite') — même
 * document, nom clarifié (voir l'enum). Aucune structure de table
 * affectée : `type` reste une simple colonne string.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('stagiaire_documents')
            ->where('type', 'lettre_motivation')
            ->update(['type' => 'lettre_stage_universite']);
    }

    public function down(): void
    {
        DB::table('stagiaire_documents')
            ->where('type', 'lettre_stage_universite')
            ->update(['type' => 'lettre_motivation']);
    }
};
