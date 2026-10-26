<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La formation existe-t-elle dans le pays, et qui peut la payer.
     *
     * Les interviews d'octobre 2026 ont montré que ce qui arrête un jeune,
     * c'est l'argent, la distance et ne jamais avoir entendu parler des
     * bourses qui existent. Une fiche qui tait ces deux points laisse croire
     * qu'il suffit de vouloir.
     */
    public function up(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $table->boolean('formation_disponible_pays')->default(true);
            $table->text('financements')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn(['formation_disponible_pays', 'financements']);
        });
    }
};
