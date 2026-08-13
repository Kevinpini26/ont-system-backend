<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique des changements de statut d'un courrier : alimenté par
 * CourrierCircuitService à chaque transition. Sert de base au calcul du
 * temps moyen de traitement par étape du circuit (dashboard).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courrier_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courrier_id')->constrained('courriers')->cascadeOnDelete();
            $table->string('statut');
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['courrier_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courrier_transitions');
    }
};
