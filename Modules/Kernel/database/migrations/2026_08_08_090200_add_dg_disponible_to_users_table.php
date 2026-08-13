<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pertinent uniquement pour l'utilisateur au poste DG : détermine si la DGA
 * peut intervenir en intérim sur le circuit courrier (voir
 * CourrierCircuitService::rendreAvisDg() et DgDisponibiliteController).
 * Défaut disponible=true : le circuit standard (DG directe) s'applique tant
 * que personne n'a explicitement activé l'intérim.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('dg_disponible')->default(true)->after('poste');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dg_disponible');
        });
    }
};
