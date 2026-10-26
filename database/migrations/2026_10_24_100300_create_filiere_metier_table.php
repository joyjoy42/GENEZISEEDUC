<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filiere_metier', function (Blueprint $table) {
            $table->foreignId('filiere_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metier_id')->constrained()->cascadeOnDelete();
            $table->primary(['filiere_id', 'metier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filiere_metier');
    }
};
