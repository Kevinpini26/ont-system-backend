<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Compteurs séquentiels par année et par type de numéro (accusé de
     * réception / enregistrement final), utilisés pour générer des
     * références uniques de façon atomique.
     */
    public function up(): void
    {
        Schema::create('courrier_numero_compteurs', function (Blueprint $table) {
            $table->string('type', 20);
            $table->unsignedSmallInteger('annee');
            $table->unsignedInteger('dernier_compteur')->default(0);
            $table->primary(['type', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courrier_numero_compteurs');
    }
};
