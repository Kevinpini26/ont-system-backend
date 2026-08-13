<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fait de `courrier_transitions` un véritable bordereau de transmission,
 * pas seulement un historique de statuts : chaque ligne porte désormais un
 * destinataire (poste, ou personne précise pour le relecteur désigné) et,
 * une fois acquittée, qui a accusé réception et quand — voir
 * CourrierCircuitService::tracerTransition()/accuserReception().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courrier_transitions', function (Blueprint $table) {
            $table->string('destinataire_poste')->nullable()->after('changed_by_id');
            $table->foreignId('destinataire_user_id')->nullable()->after('destinataire_poste')->constrained('users')->nullOnDelete();
            $table->foreignId('accuse_reception_par_id')->nullable()->after('destinataire_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('accuse_reception_at')->nullable()->after('accuse_reception_par_id');
        });
    }

    public function down(): void
    {
        Schema::table('courrier_transitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destinataire_user_id');
            $table->dropConstrainedForeignId('accuse_reception_par_id');
            $table->dropColumn(['destinataire_poste', 'accuse_reception_at']);
        });
    }
};
