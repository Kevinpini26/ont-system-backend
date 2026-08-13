<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            // Marque l'envoi de la relance "avis DG en attente depuis plus
            // de 48h" pour ne jamais la renvoyer deux fois pour un même
            // courrier (voir RelancerAvisDgEnAttenteCommand).
            $table->timestamp('relance_avis_dg_envoyee_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn('relance_avis_dg_envoyee_at');
        });
    }
};
