<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secteur_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->string('accroche');
            $table->text('le_quotidien')->nullable();
            $table->json('competences')->nullable();
            $table->json('outils')->nullable();
            $table->unsignedInteger('salaire_min')->nullable();
            $table->unsignedInteger('salaire_max')->nullable();
            $table->string('devise', 8)->default('FCFA');
            $table->text('ou_travailler')->nullable();
            $table->text('debouches')->nullable();
            $table->text('idee_fausse')->nullable();
            $table->text('realite')->nullable();
            $table->string('niveau_etude')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('vues')->default(0);
            $table->boolean('est_publie')->default(false);
            $table->timestamp('publie_le')->nullable();
            $table->timestamps();

            $table->index(['est_publie', 'secteur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metiers');
    }
};
