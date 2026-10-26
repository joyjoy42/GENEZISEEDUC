<?php

use App\Orientation\Dimension;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le profil RIASEC d'un métier : six notes de 0 à 100 qui disent à quel
     * point le métier relève de chaque grande famille d'intérêts.
     */
    public function up(): void
    {
        Schema::table('metiers', function (Blueprint $table) {
            foreach (Dimension::cases() as $dimension) {
                $table->unsignedTinyInteger($dimension->colonne())->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('metiers', function (Blueprint $table) {
            $table->dropColumn(array_map(
                fn (Dimension $dimension) => $dimension->colonne(),
                Dimension::cases()
            ));
        });
    }
};
