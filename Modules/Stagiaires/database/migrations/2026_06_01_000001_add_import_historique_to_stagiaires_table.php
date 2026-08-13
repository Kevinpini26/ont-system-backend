<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les dossiers importés depuis l'historique (import CSV, antérieurs au
 * système) ne sont jamais passés par un courrier de demande de stage : la
 * contrainte "courrier_id obligatoire" ne peut donc plus tenir. On la rend
 * nullable plutôt que de fabriquer un faux courrier de toutes pièces, ce
 * qui aurait faussé les statistiques et rapports du module Courrier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->foreignId('courrier_id')->nullable()->change();

            $table->string('origine', 20)->default('systeme')->after('courrier_id');
            $table->foreignId('importe_par_id')->nullable()->after('origine')->constrained('users')->nullOnDelete();
            $table->timestamp('importe_at')->nullable()->after('importe_par_id');
        });
    }

    /**
     * ATTENTION — ce down() échoue (violation NOT NULL) dès qu'un seul
     * stagiaire avec courrier_id = null existe, ce qui est le cas normal
     * dès qu'un import d'historique a eu lieu (voir docblock ci-dessus) :
     * la nullabilité de courrier_id est devenue un état métier légitime
     * pour cette origine-là, pas seulement un détail de schéma. Revenir sur
     * cette migration exige une décision manuelle sur ces lignes (les
     * exclure, ou leur fabriquer un rattachement) avant de pouvoir relancer
     * down() — volontairement pas automatisé ici.
     */
    public function down(): void
    {
        Schema::table('stagiaires', function (Blueprint $table) {
            $table->dropConstrainedForeignId('importe_par_id');
            $table->dropColumn(['origine', 'importe_at']);
            $table->foreignId('courrier_id')->nullable(false)->change();
        });
    }
};
