<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courriers', function (Blueprint $table) {
            $table->id();

            // Traçabilité immédiate à la réception, indépendante du numéro
            // d'enregistrement final (généré uniquement à l'étape "enregistre").
            $table->string('numero_accuse_reception')->unique();
            $table->string('numero_enregistrement')->nullable()->unique();

            $table->string('objet');
            $table->json('contenu')->nullable();
            $table->string('type', 40);
            $table->string('statut', 40);

            $table->foreignId('direction_origine_id')->nullable()->constrained('directions')->nullOnDelete();
            $table->foreignId('direction_destination_id')->nullable()->constrained('directions')->nullOnDelete();
            $table->string('expediteur_externe_nom')->nullable();

            // Métadonnées candidat, renseignées uniquement pour type=demande_stage.
            $table->string('candidat_nom')->nullable();
            $table->string('candidat_contact')->nullable();
            $table->string('candidat_etablissement')->nullable();
            $table->date('candidat_periode_debut')->nullable();
            $table->date('candidat_periode_fin')->nullable();

            $table->string('avis_dg', 20)->nullable();
            $table->text('avis_dg_commentaire')->nullable();

            $table->json('projet_reponse_contenu')->nullable();
            $table->foreignId('relecteur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('relecture_validee_at')->nullable();
            $table->text('relecture_commentaire')->nullable();

            $table->foreignId('signataire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signe_at')->nullable();

            $table->string('classification', 20)->nullable();
            $table->text('note_technique')->nullable();
            $table->string('accuse_reception_partenaire')->nullable();
            $table->timestamp('enregistre_at')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courriers');
    }
};
