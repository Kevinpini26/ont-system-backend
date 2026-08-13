<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dernière consultation d'une liste par un utilisateur (ex.
        // 'courriers_recus', 'demandes_stage') : primitive minimale pour les
        // badges de notification — "combien d'éléments sont apparus depuis
        // ma dernière visite de cette liste" — voir NotificationCompteurController.
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cle');
            $table->timestamp('consulte_at');
            $table->timestamps();

            $table->unique(['user_id', 'cle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
