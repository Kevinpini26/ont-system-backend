<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dépôt public d'une demande de stage (module Public, sans authentification) :
 * - `candidat_email` est un champ dédié, distinct de `candidat_contact`
 *   (qui reste un champ de contact libre) — c'est celui utilisé pour
 *   l'accusé de réception automatique et n'existe que pour les candidatures
 *   arrivées par ce canal.
 * - `created_by` devient nullable : un dépôt public n'a, par nature, aucun
 *   agent ONT à l'origine de sa création (à la différence d'un courrier
 *   enregistré par la Réception ou initié par une direction).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('candidat_email')->nullable()->after('candidat_contact');
            $table->foreignId('created_by')->nullable()->change();
        });
    }

    /**
     * ATTENTION — ce down() échoue (violation NOT NULL) dès qu'un seul
     * courrier avec created_by = null existe, ce qui est le cas normal dès
     * qu'un dépôt public a eu lieu (demande de stage, courrier externe) : la
     * nullabilité de created_by n'est pas un détail technique réversible
     * sans arbitrage, c'est devenu un état métier légitime et courant.
     * Revenir sur cette migration exige une décision manuelle au cas par
     * cas sur ces lignes (leur assigner un created_by de convention, ou
     * accepter de les perdre) avant de pouvoir relancer down() — ce n'est
     * volontairement pas automatisé ici.
     */
    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn('candidat_email');
            $table->foreignId('created_by')->nullable(false)->change();
        });
    }
};
