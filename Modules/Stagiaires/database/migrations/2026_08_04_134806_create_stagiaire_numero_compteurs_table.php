<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compteur séquentiel par année pour les numéros d'attestation, utilisé
 * pour générer des références uniques de façon atomique (même mécanisme
 * que courrier_numero_compteurs dans le module Courrier — dupliqué plutôt
 * que partagé pour garder les modules découplés).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaire_numero_compteurs', function (Blueprint $table) {
            $table->string('type', 20);
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('dernier_compteur')->default(0);
            $table->primary(['type', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaire_numero_compteurs');
    }
};
