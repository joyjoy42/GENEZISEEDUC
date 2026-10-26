<?php

use App\Orientation\Dimension;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les résultats sont anonymes : un jeton pour retrouver sa page, les six
     * scores, et rien d'autre. Aucun nom, aucune adresse, aucune adresse IP.
     */
    public function up(): void
    {
        Schema::create('resultat_tests', function (Blueprint $table) {
            $table->id();
            $table->string('jeton', 26)->unique();

            foreach (Dimension::cases() as $dimension) {
                $table->unsignedTinyInteger($dimension->colonne())->default(0);
            }

            $table->unsignedSmallInteger('nombre_reponses')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultat_tests');
    }
};
