<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les comptes élèves, séparés des comptes de l'équipe.
     *
     * Deux tables plutôt qu'un rôle sur `users` : une session élève ne doit
     * jamais pouvoir satisfaire le middleware qui garde le back-office.
     *
     * Le mot de passe n'est pas ici, il est chez Supabase. On ne garde que
     * de quoi relier un résultat de test à quelqu'un, et l'année de
     * naissance qui applique la règle des 15 ans.
     */
    public function up(): void
    {
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->uuid('supabase_id')->unique();
            $table->string('email')->unique();
            $table->unsignedSmallInteger('annee_naissance');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('resultat_tests', function (Blueprint $table) {
            // Un résultat reste lisible sans compte, par son jeton : la
            // colonne est nullable, et le rattachement est un bonus.
            $table->foreignId('eleve_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('resultat_tests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eleve_id');
        });

        Schema::dropIfExists('eleves');
    }
};
