<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->string('expediteur_externe_email')->nullable()->after('expediteur_externe_nom');
            $table->string('expediteur_externe_telephone')->nullable()->after('expediteur_externe_email');
            $table->string('piece_jointe_chemin')->nullable()->after('expediteur_externe_telephone');
        });
    }

    public function down(): void
    {
        Schema::table('courriers', function (Blueprint $table) {
            $table->dropColumn(['expediteur_externe_email', 'expediteur_externe_telephone', 'piece_jointe_chemin']);
        });
    }
};
