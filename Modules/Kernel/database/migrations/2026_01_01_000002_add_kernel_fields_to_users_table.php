<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 40)->after('password');
            $table->string('poste', 40)->nullable()->after('role');
            $table->foreignId('direction_id')->nullable()->after('poste')
                ->constrained('directions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('direction_id');
            $table->dropColumn(['role', 'poste']);
        });
    }
};
