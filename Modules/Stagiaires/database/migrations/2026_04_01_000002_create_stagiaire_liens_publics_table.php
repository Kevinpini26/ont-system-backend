<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mécanisme générique de lien public à usage unique, réutilisé pour la
 * signature de convention par le stagiaire et l'invitation au retour
 * d'expérience : un stagiaire n'a pas de compte utilisateur, ces actions
 * ne peuvent donc pas passer par une authentification Sanctum classique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaire_liens_publics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('token', 64)->unique();
            $table->timestamp('consomme_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaire_liens_publics');
    }
};
