<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temoignages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metier_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('fonction');
            $table->string('entreprise')->nullable();
            $table->string('ville')->default('Cotonou');
            $table->text('citation');
            $table->string('video_url')->nullable();
            $table->string('plateforme')->nullable();
            $table->string('fichier_video')->nullable();
            $table->unsignedSmallInteger('duree_secondes')->nullable();
            $table->date('date_interview')->nullable();
            $table->boolean('est_publie')->default(false);
            $table->timestamps();

            $table->index(['metier_id', 'est_publie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temoignages');
    }
};
