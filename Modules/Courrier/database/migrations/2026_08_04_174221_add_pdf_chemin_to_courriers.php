<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PDF définitif généré au moment précis de la signature (voir
 * CourrierCircuitService::signer() et DompdfCourrierPdfGenerator) — jamais
 * régénéré ni modifié ensuite : une correction après signature passe par un
 * nouveau courrier, pas par une réécriture de celui-ci.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('pdf_chemin')->nullable()->after('signe_at');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn('pdf_chemin');
        });
    }
};
