<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Numéro unique attribué à l'attestation générée à la clôture du stage,
 * utilisé par la page publique de vérification d'authenticité (QR code).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->string('numero_attestation', 20)->nullable()->unique()->after('cloture_at');
        });
    }

    public function down(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->dropColumn('numero_attestation');
        });
    }
};
