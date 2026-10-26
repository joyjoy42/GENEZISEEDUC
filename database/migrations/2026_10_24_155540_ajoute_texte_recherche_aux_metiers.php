<?php

use App\Models\Metier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Une copie du texte de la fiche, sans accents et en minuscules.
     *
     * SQLite ne sait pas comparer « mecanicien » et « Mécanicien » : sans
     * cette colonne, un élève qui tape sans accents — c'est-à-dire presque
     * tout le monde sur téléphone — ne trouve rien.
     */
    public function up(): void
    {
        Schema::table('metiers', function (Blueprint $table) {
            $table->text('texte_recherche')->nullable();
        });

        // Mise à jour directe : save() toucherait updated_at de toutes les
        // fiches, et saveQuietly() ne déclencherait pas le hook qui calcule
        // la colonne.
        Metier::chunkById(100, function ($metiers) {
            foreach ($metiers as $metier) {
                DB::table('metiers')
                    ->where('id', $metier->id)
                    ->update(['texte_recherche' => $metier->texteRecherchable()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('metiers', function (Blueprint $table) {
            $table->dropColumn('texte_recherche');
        });
    }
};
