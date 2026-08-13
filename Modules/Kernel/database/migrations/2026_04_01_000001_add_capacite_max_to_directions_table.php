<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            // Nul = aucune limite. Sert au quota d'affectation de stagiaires
            // (Modules\Stagiaires\Contracts\AffectationRules::estSaturee).
            $table->unsignedInteger('capacite_max')->nullable()->after('actif');
        });
    }

    public function down(): void
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->dropColumn('capacite_max');
        });
    }
};
