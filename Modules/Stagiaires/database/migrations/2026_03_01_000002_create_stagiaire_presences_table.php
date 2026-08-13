<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une ligne par jour (date_debut = date_fin) ou par période
     * (date_fin > date_debut), enregistrée par la direction d'accueil.
     */
    public function up(): void
    {
        Schema::create('stagiaire_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->cascadeOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('present')->default(true);
            $table->text('commentaire')->nullable();
            $table->foreignId('enregistre_par_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaire_presences');
    }
};
